<?php

declare(strict_types=1);

namespace App\Service;

use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ManagerRegistry;

class GeoNamesService extends EntityService
{
    private Connection $connection;

    public function __construct(ManagerRegistry $doctrine)
    {
        // https://symfony.com/doc/current/doctrine/multiple_entity_managers.html
        $this->connection = $doctrine->getManager('geonames')->getConnection();
    }

    // GeoNames Feature Codes: http://www.geonames.org/export/codes.html
    public function reverseGeocode(float $latitude, float $longitude): array
    {
        $return = [];

        // H: Stream, lake, ...
//        if ($h = $this->getGeoname($latitude, $longitude, ['fclass' => 'H'], 1)) {
//            $return[] = $h;
//        }

        // L: Park, area, ...
//        if ($l = $this->getGeoname($latitude, $longitude, ['fclass' => 'L'], 1)) {
//            $return[] = $l;
//        }

        // T: Mountain, hill, rock, ...
        if ($t = $this->getGeoname($latitude, $longitude, ['fclass' => 'T'], 1)) {
            $return[] = $t;
        }

        // V: Forest, heath, ...
        if ($v = $this->getGeoname($latitude, $longitude, ['fclass' => 'V'], 1)) {
            $return[] = $v;
        }

        // P: City, village, ...
        if ($pplx = $this->getGeoname($latitude, $longitude, ['fcode' => 'PPLX'], 5)) {
            $return[] = $pplx;
            $return[] = $this->getParent($pplx['id']);
        }

        // A: Country, state, region, ...
        if ($adm4 = $this->getGeoname($latitude, $longitude, ['fcode' => 'ADM4'], 5)) {
            $return[] = $adm4;
//            $return[] = $adm3 = $this->getParent($adm4['geoname_id']);
//            $return[] = $adm2 = $this->getParent($adm3['geoname_id']);
//            $return[] = $adm1 = $this->getParent($adm2['geoname_id']);
//            $return[] = $adm1 = $this->getParent($adm1['geoname_id']);
        }

        return $return;
    }

    private function getGeoname($latitude, $longitude, $where = [], $maxDistance = null)
    {
        $wheres = $params = [];

        // First - cut bounding box (in degrees).
        // https://www.movable-type.co.uk/scripts/latlong-db.html
        // The radius of the bounding circle (in km):
        $rad = $maxDistance ?? 1.5;
        // The radius of the Earth (in km):
        $R = 6371;
        $maxLat = $latitude + rad2deg($rad/$R);
        $minLat = $latitude - rad2deg($rad/$R);
        $maxLon = $longitude + rad2deg(asin($rad/$R) / cos(deg2rad($latitude)));
        $minLon = $longitude - rad2deg(asin($rad/$R) / cos(deg2rad($latitude)));

        $wheres[] = 'TRUE';
        foreach ($where as $key => $value) {
            $wheres[] = "$key = :$key";
            $params[$key] = $value;
        }

        $sql = "
            SELECT 
                *,
                3963.191 * ACOS((SIN(radians(:lat))*SIN(radians(latitude))) +(COS(radians(:lat))*cos(radians(latitude))*COS(radians(longitude)-radians(:lon)))) AS distance
            FROM 
                geonames
            WHERE
                latitude BETWEEN :minLat AND :maxLat
                AND longitude BETWEEN :minLon AND :maxLon
                AND ".implode(' AND ', $wheres)."
            ORDER BY
                distance
            LIMIT 
                1
        ";

        $resultSet = $this->connection->executeQuery($sql, [
            'minLat' => $minLat,
            'maxLat' => $maxLat,
            'minLon' => $minLon,
            'maxLon' => $maxLon,
            'lon' => $longitude,
            'lat' => $latitude,
        ] + $params);

        return $resultSet->fetchAssociative();
    }

    private function getParent($geonameId, $relationType = '')
    {
        $sql = "
            SELECT 
                geonames.*
            FROM 
                hierarchy
                JOIN geonames ON (hierarchy.parent_id = geonames.id)
            WHERE
                hierarchy.child_id = :child_id
            LIMIT 
                1
        ";

        $resultSet = $this->connection->executeQuery($sql, [
            'child_id' => $geonameId,
        ]);

        return $resultSet->fetchAssociative();
    }

}
