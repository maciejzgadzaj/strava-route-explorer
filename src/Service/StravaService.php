<?php

namespace App\Service;

use App\Entity\Athlete;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Class StravaService
 *
 * @package App\Service
 */
class StravaService
{
    /**
     * @var \Psr\Container\ContainerInterface
     */
    private $container;

    /**
     * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
     */
    private $session;

    /**
     * @var string
     */
    public $stravaRefreshToken;

    /**
     * @var \App\Service\AthleteService
     */
    private $athleteService;

    /**
     * StravaService constructor.
     *
     * @param \Psr\Container\ContainerInterface $container
     * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
     * @param $stravaRefreshToken
     * @param \App\Service\AthleteService $athleteService
     */
    public function __construct(
        ContainerInterface $container,
        SessionInterface $session,
        $stravaRefreshToken,
        AthleteService $athleteService
    ) {
        $this->container = $container;
        $this->session = $session;
        $this->stravaRefreshToken = $stravaRefreshToken;
        $this->athleteService = $athleteService;
    }

    /**
     * Send request to Strava API.
     *
     * @param string $method
     * @param string $uri
     * @param array $options
     *
     * @return \Psr\Http\Message\ResponseInterface
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function apiRequest($method, $uri, $options = [])
    {
        /** @var \GuzzleHttp\Client $client */
        $client = $this->container->get('csa_guzzle.client.strava');

        return $client->$method($uri, $options);
    }

    /**
     * Return athlete's access_token (after refreshing it if needed).
     *
     * @param \App\Entity\Athlete $athlete
     *
     * @return string
     *
     * @throws \Exception
     */
    public function getAthleteAccessToken($athlete)
    {
        // No access_token means an athlete has never authorized so far.
        if (empty($athlete->getAccessToken())) {
            return;
        }

        // https://developers.strava.com/docs/authentication/#refresh-expired-access-tokens
        // suggests refreshing an existing access_token
        // within 1 hour of its expiration time.
        $within_1_hr = new \DateTime('+1 hour');

        if (empty($athlete->getExpiresAt()) || $athlete->getExpiresAt() < $within_1_hr) {
            $athlete = $this->refreshAccessToken($athlete);
        }

        return $athlete->getAccessToken();
    }

    /**
     * Refresh athlete's access_token.
     *
     * @param \App\Entity\Athlete|null $athlete
     * @param string|null $refreshToken
     *
     * @return \App\Entity\Athlete|string
     */
    public function refreshAccessToken(Athlete $athlete = null, $refreshToken = null)
    {
        if (!empty($athlete)) {
            // To refresh short-lived access_token, we need to send user's refresh_token.
            $refreshToken = $athlete->getRefreshToken();
            // For users still using old "forever tokens", we need to migrate them
            // to the new system, sending old forever token as a refresh token.
            if (empty($refreshToken)) {
                $refreshToken = $athlete->getAccessToken();
            }
        }

        /** @var \GuzzleHttp\Client $client */
        $client = $this->container->get('csa_guzzle.client.strava');
        $params = [
            'client_id' => $this->container->getParameter('strava_client_id'),
            'client_secret' => $this->container->getParameter('strava_client_secret'),
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
        ];
        $response = $client->post('/oauth/token', ['form_params' => $params]);
        $content = \GuzzleHttp\json_decode($response->getBody()->getContents());

        return !empty($athlete) ? $this->athleteService->saveTokenData($athlete, $content) : $content->access_token;
    }

    /**
     * Fetch all athlete routes data from Strava API.
     *
     * @param Athlete $athlete
     *
     * @return array
     *
     * @throws \Exception
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function getAthleteRoutes(Athlete $athlete)
    {
        $perPage = 200;

        // Athlete has not connected his Strava account yet.
        if (!$athleteAccessToken = $this->getAthleteAccessToken($athlete)) {
            throw new \Exception(strtr('No token found for %athlete_name% (%athlete_id%).', [
                '%athlete_name%' => $athlete->getName(),
                '%athlete_id%' => $athlete->getId(),
            ]));
        }

        // Use athlete-specific access token to fetch their routes.
        $options = [
            'headers' => [
                'Authorization' => 'Bearer '.$athleteAccessToken,
            ],
        ];
        $page = 1;

        do {
            $response = $this->apiRequest(
                'get',
                '/api/v3/athletes/'.$athlete->getId().'/routes?per_page='.$perPage.'&page='.$page,
                $options
            );
            $content = \GuzzleHttp\json_decode($response->getBody()->getContents());

            $routes = array_merge($routes ?? [], $content);

            $page++;
        } while (!empty($content) && count($content) == $perPage);

        // Key return array by route id.
        $return = [];
        foreach ($routes as $route) {
            $return[$route->id] = $route;
        }

        return $return;
    }

    /**
     * Fetch route data from Strava API.
     *
     * @param int $routeId
     *
     * @return mixed
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function getRoute($routeId)
    {
        // Use app's "refresh_token" to get new "access_token".
        $accessToken = $this->refreshAccessToken(null, $this->stravaRefreshToken);

        $options = [
            'headers' => [
                'Authorization' => 'Bearer '.$accessToken,
            ],
        ];
        $response = $this->apiRequest('get', '/api/v3/routes/'.$routeId, $options);
        return \GuzzleHttp\json_decode($response->getBody()->getContents());
    }
}
