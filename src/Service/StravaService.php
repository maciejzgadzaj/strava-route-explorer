<?php

namespace App\Service;

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
     * StravaService constructor.
     *
     * @param \Psr\Container\ContainerInterface $container
     * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
     */
    public function __construct(ContainerInterface $container, SessionInterface $session, $stravaAccessToken)
    {
        $this->container = $container;
        $this->session = $session;
        $this->stravaAccessToken = $stravaAccessToken;
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
