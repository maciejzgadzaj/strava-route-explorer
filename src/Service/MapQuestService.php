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
 * Class MapQuestService
 *
 * @package App\Service
 */
class MapQuestService
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
     * StravaService constructor.
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
     * Get static map with polyline overlaid.
     *
     * @param string $polyline
     *
     * @return \Psr\Http\Message\ResponseInterface
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function getStaticMapWithPolyline($polyline)
    {
        $queryData = [
            'key' => $this->mapQuestConsumerKey,
            'size' => '225,150',
            'shape' => 'border:ff0000|weight:3|cmp|enc:'.$polyline,
        ];

        $uri = 'https://www.mapquestapi.com/staticmap/v5/map?'.http_build_query($queryData);

        $response = $this->apiRequest('get', $uri);

        return $response->getBody()->getContents();
    }

    /**
     * @param $name
     *
     * @return array
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     *
     * @see https://developer.mapquest.com/documentation/geocoding-api/
     * @see https://developer.mapquest.com/documentation/samples/geocoding/v1/address/
     */
    public function getLocationsByName($name)
    {
        $queryData = [
            'key' => $this->mapQuestConsumerKey,
            'inFormat' => 'kvp',
            'outFormat' => 'json',
            'location' => $name,
            'thumbMaps' => 'false',
        ];

        $uri = '/geocoding/v1/address?'.http_build_query($queryData);

        $response = $this->apiRequest('get', $uri);
        $content = \GuzzleHttp\json_decode($response->getBody()->getContents());

        $return = [];
        if (!empty($content->results[0]->locations)) {
            foreach ($content->results[0]->locations as $location) {
                $nameElements = [
                    $location->street,
                    $location->adminArea6,
                    $location->adminArea5,
                    $location->adminArea4,
                    $location->adminArea3,
                    $location->adminArea1,
                ];
                $nameElements = array_filter($nameElements);

                $return[] = (object) [
                    'label' => implode(', ', $nameElements),
                    'latitude' => $location->latLng->lat,
                    'longitude' => $location->latLng->lng,
                ];
            }
        }

        return $return;
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
        $client = $this->container->get('csa_guzzle.client.mapquest');

        return $client->$method($uri, $options);
    }
}
