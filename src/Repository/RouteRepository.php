<?php

namespace App\Repository;

use App\Entity\Route;
use CrEOF\Spatial\PHP\Types\Geometry\Point;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Class RouteRepository
 *
 * @package App\Repository
 */
class RouteRepository extends ServiceEntityRepository
{
    /**
     * RouteRepository constructor.
     *
     * @param \Symfony\Bridge\Doctrine\RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Route::class);
    }

    /**
     * Find Route entities filtered and sorted by a set criteria .
     *
     * @param array $criteria
     * @param array|null $orderBy
     * @param int $limit
     * @param int $offset
     *
     * @return array
     */
    public function findByFilters(array $criteria, ?array $orderBy = null, $limit = 50, $offset = 0)
    {
        // Apparently subqueries cannot be used in Doctrine for filtering
        // and ordering, so we have switched to DQL instead.
        // https://symfony.com/doc/current/doctrine.html#querying-with-dql-or-sql
        $entityManager = $this->getEntityManager();

        $sqls = $selects = $joins = $wheres = $havings = $orders = $parameters = [];

        $selects[] = 'r AS route';

        foreach ($criteria as $key => $value) {
            if (empty($value)) {
                continue;
            }

            switch ($key) {
                case 'type':
                    $wheres[] = 'r.type = :type';
                    $parameters['type'] = $value;
                    break;

                case 'name':
                    $wheres[] = '(r.name LIKE :name OR r.id = :id)';
                    $parameters['name'] = '%'.$value.'%';
                    $parameters['id'] = $value;
                    break;

                case 'distance_min':
                    $wheres[] = 'r.distance > :min_distance';
                    $parameters['min_distance'] = $value * 1000;
                    break;

                case 'distance_max':
                    $wheres[] = 'r.distance < :max_distance';
                    $parameters['max_distance'] = $value * 1000;
                    break;

                case 'elevation_gain_min':
                    $wheres[] = 'r.elevationGain > :elevation_gain_min';
                    $parameters['elevation_gain_min'] = $value;
                    break;

                case 'elevation_gain_max':
                    $wheres[] = 'r.elevationGain < :elevation_gain_max';
                    $parameters['elevation_gain_max'] = $value;
                    break;

                case 'athlete':
                    $joins['athlete'] = 'JOIN r.athlete a';
                    $wheres[] = '(a.name LIKE :athlete_name OR a.id = :athlete_id)';
                    $parameters['athlete_name'] = '%'.$value.'%';
                    $parameters['athlete_id'] = $value;
                    break;

                case 'start_dist':
                    if (!empty($criteria['start_latlon'])) {
                        $start = new Point(array_reverse(explode(',', $criteria['start_latlon'])));
                        $selects['start_dist'] = '
                            ( 3959 * acos(cos(radians('.$start->getLatitude().'))
                            * cos( radians( x(r.start) ) )
                            * cos( radians( y(r.start) )
                            - radians('.$start->getLongitude().') )
                            + sin( radians('.$start->getLatitude().') )
                            * sin( radians( x(r.start) ) ) ) ) AS start_dist
                        ';
                        $havings[] = 'start_dist < :start_dist';
                        $parameters['start_dist'] = $value;
                        $orders[] = 'start_dist ASC';
                    }
                    break;

                case 'start':
                    if (!empty($criteria['start_latlon'])) {
                        $start = new Point(array_reverse(explode(',', $criteria['start_latlon'])));
                        $selects['start_dist'] = '
                            ( 3959 * acos(cos(radians('.$start->getLatitude().'))
                            * cos( radians( x(r.start) ) )
                            * cos( radians( y(r.start) )
                            - radians('.$start->getLongitude().') )
                            + sin( radians('.$start->getLatitude().') )
                            * sin( radians( x(r.start) ) ) ) ) AS start_dist
                        ';
                        $orders[] = 'start_dist ASC';
                    }
                    break;

                case 'end_dist':
                    if (!empty($criteria['end_latlon'])) {
                        $end = new Point(array_reverse(explode(',', $criteria['end_latlon'])));
                        $selects['end_dist'] = '
                            ( 3959 * acos(cos(radians('.$end->getLatitude().'))
                            * cos( radians( x(r.end) ) )
                            * cos( radians( y(r.end) )
                            - radians('.$end->getLongitude().') )
                            + sin( radians('.$end->getLatitude().') )
                            * sin( radians( x(r.end) ) ) ) ) AS end_dist
                        ';
                        $havings[] = 'end_dist < :end_dist';
                        $parameters['end_dist'] = $value;
                        $orders[] = 'end_dist ASC';
                    }
                    break;

                case 'end':
                    if (!empty($criteria['end_latlon'])) {
                        $end = new Point(array_reverse(explode(',', $criteria['end_latlon'])));
                        $selects['end_dist'] = '
                            ( 3959 * acos(cos(radians('.$end->getLatitude().'))
                            * cos( radians( x(r.end) ) )
                            * cos( radians( y(r.end) )
                            - radians('.$end->getLongitude().') )
                            + sin( radians('.$end->getLatitude().') )
                            * sin( radians( x(r.end) ) ) ) ) AS end_dist
                        ';
                        $orders[] = 'end_dist ASC';
                    }
                    break;
            }
        }

        // Add sorts.
        if (!empty($orderBy)) {
            foreach ($orderBy as $field => $direction) {
                if ($field == 'a.name') {
                    $joins['athlete'] = 'JOIN r.athlete a';
                }
                $orders[] = $field.' '.$direction;
            }
        }

        // Build query SQL.
        $sqls[] = 'SELECT '.implode(', ', $selects).' FROM App:Route r';
        if (!empty($joins)) {
            $sqls[] = implode(' ', $joins);
        }
        if (!empty($wheres)) {
            $sqls[] = 'WHERE '.implode(' AND ', $wheres);
        }
        if (!empty($havings)) {
            $sqls[] = 'HAVING '.implode(' AND ', $havings);
        }
        if (!empty($orders)) {
            $sqls[] = 'ORDER BY '.implode(', ', $orders);
        }

        // Create query.
        $sql = implode(' ', $sqls);
        $query = $entityManager->createQuery($sql);

        // Add parameters.
        foreach ($parameters as $key => $value) {
            $query->setParameter($key, $value);
        }

        // Paginate.
        $paginator = new Paginator($query);

        // Return results.
        return [
            'total' => count($paginator),
            'pages' => ceil(count($paginator) / $limit),
            'results' => $paginator
                ->getQuery()
                ->setFirstResult($offset)
                ->setMaxResults($limit)
                ->getResult(),
        ];
    }
}
