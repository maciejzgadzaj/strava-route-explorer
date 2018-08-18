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
    private $stravaAccessToken;

    /**
     * @var \App\Service\AthleteService
     */
    private $athleteService;

    /**
     * StravaService constructor.
     *
     * @param \Psr\Container\ContainerInterface $container
     * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
     * @param $stravaAccessToken
     * @param \App\Service\AthleteService $athleteService
     */
    public function __construct(
        ContainerInterface $container,
        SessionInterface $session,
        $stravaAccessToken,
        AthleteService $athleteService
    ) {
        $this->container = $container;
        $this->session = $session;
        $this->stravaAccessToken = $stravaAccessToken;
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
        if (!$athleteAccessToken = $athlete->getAccessToken()) {
            throw new \Exception(strtr('No token found for athlete %athlete_id%.', [
                '%athlete_id%' => $athleteId,
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
        $options = [
            'headers' => [
                'Authorization' => 'Bearer '.$this->stravaAccessToken,
            ],
        ];
        $response = $this->apiRequest('get', '/api/v3/routes/'.$routeId, $options);
        return \GuzzleHttp\json_decode($response->getBody()->getContents());
    }
}
