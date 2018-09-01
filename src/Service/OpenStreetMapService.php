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
 * Class OpenStreetMapService
 *
 * @package App\Service
 */
class OpenStreetMapService
{
    /**
     * @var \Psr\Container\ContainerInterface
     */
    private $container;

    /**
     * @var string
     */
    private $mapQuestConsumerKey;

    /**
     * OpenStreetMapService constructor.
     *
     * @param \Psr\Container\ContainerInterface $container
     * @param string $mapQuestConsumerKey
     */
    public function __construct(ContainerInterface $container, $mapQuestConsumerKey)
    {
        $this->container = $container;
        $this->mapQuestConsumerKey = $mapQuestConsumerKey;
    }

    /**
     * Search OSM data by name and address.
     *
     * @param string $name
     *
     * @return array
     *
     * @see https://nominatim.org/release-docs/develop/api/Search/
     * @see https://wiki.openstreetmap.org/wiki/Nominatim
     */
    public function getLocationsByName($name)
    {
        $queryParams = [
            'q' => $name,
            'format' => 'json',
        ];

        $uri = '/search?'.http_build_query($queryParams);

        $response = $this->apiRequest('get', $uri);
        $content = \GuzzleHttp\json_decode($response->getBody()->getContents());

        $return = [];
        if (!empty($content)) {
            foreach ($content as $location) {
                $return[] = (object) [
                    'label' => $location->display_name,
                    'latitude' => $location->lat,
                    'longitude' => $location->lon,
                ];
            }
        }

        return $return;
    }

    /**
     * Reverse geocode.
     *
     * @param float $lat
     * @param float $lon
     *
     * @return array
     *
     * @see https://nominatim.org/release-docs/develop/api/Reverse/
     * @see https://wiki.openstreetmap.org/wiki/Nominatim
     */
    public function reverseGeocode($lat, $lon)
    {
        $queryParams = [
            'lat' => $lat,
            'lon' => $lon,
            'format' => 'json',
        ];

        $uri = '/reverse?'.http_build_query($queryParams);

        $response = $this->apiRequest('get', $uri);
        return \GuzzleHttp\json_decode($response->getBody()->getContents());
    }

    /**
     * Send request to OpenStreetMap API.
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
        $client = $this->container->get('csa_guzzle.client.openstreetmap.nominatim');

        return $client->$method($uri, $options);
    }
}
