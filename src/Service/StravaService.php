<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Athlete;
use Doctrine\Common\Collections\ArrayCollection;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class StravaService
{
    public function __construct(
        private readonly HttpClientInterface $stravaClient,
        private readonly AthleteService $athleteService,
        private readonly LoggerInterface $logger,
        private readonly string $stravaClientId,
        private readonly string $stravaClientSecret,
        private readonly string $stravaRefreshToken,
    ) {
    }

    public function getAthleteAccessToken(Athlete $athlete): ?string
    {
        // No access_token means an athlete has never authorized so far.
        if (empty($athlete->getAccessToken())) {
            return null;
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

    public function refreshAccessToken(Athlete $athlete = null, string $refreshToken = null): Athlete|string
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

        $params = [
            'client_id' => $this->stravaClientId,
            'client_secret' => $this->stravaClientSecret,
            'grant_type' => 'refresh_token',
            'refresh_token' => $refreshToken,
        ];

        try {
            $response = $this->stravaClient->request('POST', '/oauth/token', ['body' => $params]);
        } catch (ClientException $clientException) {
            $this->logger->error('Error refreshing user access token', [
                'exception' => $clientException->getMessage(),
            ]);
            throw $clientException;
        }

        $content = $response->toArray();

        return !empty($athlete)
            ? $this->athleteService->saveTokenData($athlete, $content)
            : $content['access_token'];
    }

    public function fetchAthleteRoutes(Athlete $athlete, $page = 1, $perPage = 50): ArrayCollection
    {
        // Athlete has not connected his Strava account yet.
        if (!$athleteAccessToken = $this->getAthleteAccessToken($athlete)) {
            $this->logger->error('Athlete has not connected his Strava account yet');
            throw new Exception(strtr('No token found for %athlete_name% (%athlete_id%).', [
                '%athlete_name%' => $athlete->getName(),
                '%athlete_id%' => $athlete->getId(),
            ]));
        }

        $options = [
            'headers' => [
                'Authorization' => 'Bearer '.$athleteAccessToken,
            ],
            'query' => [
                'page' => $page,
                'per_page' => $perPage,
            ],
        ];

        $response = $this->stravaClient->request('GET', '/api/v3/athletes/'.$athlete->getId().'/routes', $options);

        return new ArrayCollection($response->toArray());
    }

    public function getAthlete(int $athleteId): array
    {
        $athlete = $this->athleteService->load($athleteId);

        $options = [
            'headers' => [
                'Authorization' => 'Bearer '.$this->getAthleteAccessToken($athlete),
            ],
        ];

        $response = $this->stravaClient->request('GET', '/api/v3/athlete', $options);

        return $response->toArray();
    }

    public function getRoute(int $routeId): array
    {
        // Use app's "refresh_token" to get new "access_token".
        $accessToken = $this->refreshAccessToken(null, $this->stravaRefreshToken);

        $options = [
            'headers' => [
                'Authorization' => 'Bearer '.$accessToken,
            ],
        ];
        $response = $this->stravaClient->request('GET', '/api/v3/routes/'.$routeId, $options);

        return $response->toArray();
    }
}
