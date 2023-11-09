<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class MapQuestService
{
    public function __construct(
        private HttpClientInterface $client,
        private readonly string $mapQuestConsumerKey,
    ) {
    }

    /**
     * Get static map with polyline overlaid.
     */
    public function getStaticMapWithPolyline(string $polyline): string
    {
        $queryData = [
            'key' => $this->mapQuestConsumerKey,
            'size' => '225,150',
            'shape' => 'border:ff0000|weight:3|cmp|enc:'.$polyline,
        ];

        $uri = 'https://www.mapquestapi.com/staticmap/v5/map?'.http_build_query($queryData);

        $response = $this->client->request('GET', $uri);

        return $response->getContent();
    }

    /**
     * @see https://developer.mapquest.com/documentation/geocoding-api/
     * @see https://developer.mapquest.com/documentation/samples/geocoding/v1/address/
     */
    public function getLocationsByName(string $name): array
    {
        $queryData = [
            'key' => $this->mapQuestConsumerKey,
            'inFormat' => 'kvp',
            'outFormat' => 'json',
            'location' => $name,
            'thumbMaps' => 'false',
        ];

        $uri = '/geocoding/v1/address?'.http_build_query($queryData);

        $response = $this->client->request('GET', $uri);

        $content = $response->toArray();

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
}
