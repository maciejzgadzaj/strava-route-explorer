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
 * Class PhotonService
 *
 * @see http://photon.komoot.de/
 *
 * @package App\Service
 */
class PhotonService
{
    /**
     * @var \Psr\Container\ContainerInterface
     */
    private $container;

    /**
     * PhotonService constructor.
     *
     * @param \Psr\Container\ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
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
            'limit' => 10,
        ];

        $uri = '/api/?'.http_build_query($queryParams);

        $response = $this->apiRequest('get', $uri);
        $content = \GuzzleHttp\json_decode($response->getBody()->getContents());

        $return = [];
        if (!empty($content->features)) {
            foreach ($content->features as $feature) {
                $return[] = $this->featureToResponseObject($feature);
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
        ];

        $uri = '/reverse?'.http_build_query($queryParams);

        $response = $this->apiRequest('get', $uri);
        $content = \GuzzleHttp\json_decode($response->getBody()->getContents());

        $feature = reset($content->features);

        return $this->featureToResponseObject($feature);
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
        $client = $this->container->get('csa_guzzle.client.photon');

        return $client->$method($uri, $options);
    }

    /**
     * Convert Photon response single feature row into local unified response object.
     *
     * @param object $feature
     *
     * @return object
     */
    private function featureToResponseObject($feature)
    {
        return (object) [
            'name' => $feature->properties->name,
            'class' => str_replace('_', ' ', $feature->properties->osm_value) ?? null,
            'city' => $feature->properties->city ?? $feature->properties->state,
            'country' => $feature->properties->country,
            'latitude' => $feature->geometry->coordinates[1],
            'longitude' => $feature->geometry->coordinates[0],
        ];
    }
}
