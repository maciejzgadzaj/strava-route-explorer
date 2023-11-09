<?php

declare(strict_types=1);

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @see http://photon.komoot.de/
 */
class PhotonService
{
    public function __construct(
        private readonly HttpClientInterface $photonClient,
        protected readonly LoggerInterface $logger,
    ) {
    }

    /**
     * Search OSM data by name and address.
     *
     * @see https://nominatim.org/release-docs/develop/api/Search/
     * @see https://wiki.openstreetmap.org/wiki/Nominatim
     */
    public function getLocationsByName(string $name): array
    {
        $queryParams = [
            'q' => $name,
            'limit' => 10,
        ];

        $uri = '/api/?'.http_build_query($queryParams);

        $response = $this->photonClient->request('GET', $uri);
        $content = $response->toArray();

        $return = [];
        foreach ($content['features'] as $feature) {
            $return[] = $this->featureToResponseArray($feature);
        }

        return $return;
    }

    /**
     * @see https://nominatim.org/release-docs/develop/api/Reverse/
     * @see https://wiki.openstreetmap.org/wiki/Nominatim
     */
    public function reverseGeocode(float $lat, float $lon): array
    {
        $queryParams = [
            'lat' => $lat,
            'lon' => $lon,
        ];

        $uri = '/reverse?'.http_build_query($queryParams);

        $response = $this->photonClient->request('GET', $uri);
        $content = $response->toArray();

        $feature = reset($content['features']);

        return $this->featureToResponseArray($feature);
    }

    /**
     * Convert Photon response single feature row into local unified response object.
     */
    private function featureToResponseArray(array $feature): array
    {
        return [
            'name' => $feature['properties']['name'],
            'class' => ucfirst(str_replace('_', ' ', $feature['properties']['osm_value'])) ?? null,
            'city' => $feature['properties']['city'] ?? $feature['properties']['county'] ?? $feature['properties']['state'] ?? null,
            'country' => $feature['properties']['country'],
            'latitude' => $feature['geometry']['coordinates'][1],
            'longitude' => $feature['geometry']['coordinates'][0],
        ];
    }
}
