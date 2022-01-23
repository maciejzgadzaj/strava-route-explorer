<?php

namespace App\Service;

use App\Entity\Athlete;
use App\Entity\Route;
use Doctrine\DBAL\FetchMode;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\RequestOptions;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Class GeoNamesService
 *
 * GeoNames WebServices overview
 * http://www.geonames.org/export/ws-overview.html
 *
 * GeoNames Web Services Documentation
 * http://www.geonames.org/export/web-services.html
 *
 * Data and Terms and Conditions:
 * https://www.geonames.org/export/
 *
 * free : GeoNames data is free, the data is available without costs.
 *
 * cc-by licence (creative commons attributions license). You should give credit to GeoNames
 * when using data or web services with a link or another reference to GeoNames.
 *
 * commercial usage is allowed
 *
 * 'as is' : The data is provided "as is" without warranty or any representation of accuracy,
 * timeliness or completeness.
 *
 * 30'000 credits daily limit per application (identified by the parameter 'username'),
 * the hourly limit is 2000 credits. A credit is a web service request hit for most services.
 * An exception is thrown when the limit is exceeded.
 *
 * Service Level Agreement is available for our premium web services.
 *
 * @package App\Service
 */
class GeoNamesService extends EntityService
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $connection;

    /**
     * GeoNamesService constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->connection = $entityManager->getConnection();
    }

    /**
     * @param $locations
     *
     * extendedFindNearby = 4 credits per request
     * https://www.geonames.org/export/credits.html
     *
     * @see https://developer.mapquest.com/documentation/open/geocoding-api/batch/post/
     */
    public function reverseGeocode($latitude, $longitude)
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
            $return[] = $this->getParent($pplx['geonameid']);
        }

        // A: Country, state, region, ...
        if ($adm4 = $this->getGeoname($latitude, $longitude, ['fcode' => 'ADM4'], 5)) {
            $return[] = $adm4;
//            $return[] = $adm3 = $this->getParent($adm4['geonameid']);
//            $return[] = $adm2 = $this->getParent($adm3['geonameid']);
//            $return[] = $adm1 = $this->getParent($adm2['geonameid']);
//            $return[] = $adm1 = $this->getParent($adm1['geonameid']);
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
                geoname
            WHERE
                latitude BETWEEN :minLat AND :maxLat
                AND longitude BETWEEN :minLon AND :maxLon
                AND ".implode(' AND ', $wheres)."
            ORDER BY
                distance
            LIMIT 
                1
        ";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([
            'minLat' => $minLat,
            'maxLat' => $maxLat,
            'minLon' => $minLon,
            'maxLon' => $maxLon,
            'lon' => $longitude,
            'lat' => $latitude,
        ] + $params);
        $result = $stmt->fetch();

        return $result;
    }

    private function getParent($geonameId, $relationType = '')
    {
        $sql = "
            SELECT 
                g.*
            FROM 
                hierarchy h
                JOIN geoname g ON (h.parentId = g.geonameid)
            WHERE
                h.childId = :childId
            LIMIT 
                1
        ";
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([
            'childId' => $geonameId,
        ]);
        return $stmt->fetch();
    }

}
