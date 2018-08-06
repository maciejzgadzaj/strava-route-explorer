<?php

namespace App\Service;

use App\Entity\Athlete;
use App\Entity\Route;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
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
     * StravaService constructor.
     *
     * @param \Psr\Container\ContainerInterface $container
     * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
     */
    public function __construct(ContainerInterface $container, SessionInterface $session)
    {
        $this->container = $container;
        $this->session = $session;
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

        if (empty($options['headers']['Authorization'])) {
            $options['headers']['Authorization'] = 'Bearer '.$this->session->get('strava_access_token');
        }

        return $client->$method($uri, $options);
    }
}
