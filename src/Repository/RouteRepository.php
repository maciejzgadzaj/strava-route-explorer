<?php

namespace App\Repository;

use App\Entity\Route;
use App\Service\AthleteService;
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
     * @var \App\Service\AthleteService
     */
    private $athleteService;

    /**
     * RouteRepository constructor.
     *
     * @param \Symfony\Bridge\Doctrine\RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry, AthleteService $athleteService)
    {
        parent::__construct($registry, Route::class);

        $this->athleteService = $athleteService;
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
        $currentAthlete = $this->athleteService->getCurrentAthlete();

        // Apparently subqueries cannot be used in Doctrine for filtering
        // and ordering, so we have switched to DQL instead.
        // https://symfony.com/doc/current/doctrine.html#querying-with-dql-or-sql
        $entityManager = $this->getEntityManager();

        $sqls = $selects = $joins = $wheres = $havings = $orders = $parameters = [];

        $selects[] = 'r AS route';
        $wheres[] = 'r.public = TRUE';

        foreach ($criteria as $key => $value) {
            if (empty($value)) {
                continue;
            }

            switch ($key) {
                case 'type':
                    $values = explode(',', $value);
                    $wheres[] = 'r.type = :type';
                    $parameters['type'] = $values[0];
                    if (!empty($values[1])) {
                        $wheres[] = 'r.subType = :sub_type';
                        $parameters['sub_type'] = $values[1];
                    }
                    break;

                case 'name':
                    $selects[] = 'MATCH (r.name) AGAINST (:name BOOLEAN) AS name_score';
                    $selects[] = 'MATCH (r.description) AGAINST (:name BOOLEAN) AS description_score';
                    $selects[] = 'MATCH (r.segments) AGAINST (:name BOOLEAN) AS segments_score';
                    $selects[] = 'MATCH (r.tags) AGAINST (:name BOOLEAN) AS tags_score';
                    $wheres[] = '(r.id = :id OR MATCH (r.name, r.description, r.segments, r.tags) AGAINST (:name BOOLEAN) > 0)';
                    $parameters['id'] = $value;
                    $parameters['name'] = $value;
                    if (empty($orderBy)) {
                        $orderBy = [
                            'name_score * 100 + segments_score * 10 + tags_score * 10 + description_score' => 'DESC',
                        ];
                    }
                    break;

                case 'distance_min':
                    $wheres[] = 'r.distance > :min_distance';
                    $parameters['min_distance'] = $value * 1000;
                    break;

                case 'distance_max':
                    $wheres[] = 'r.distance < :max_distance';
                    $parameters['max_distance'] = $value * 1000;
                    break;

                case 'ascent_min':
                    $wheres[] = 'r.ascent > :ascent_min';
                    $parameters['ascent_min'] = $value;
                    break;

                case 'ascent_max':
                    $wheres[] = 'r.ascent < :ascent_max';
                    $parameters['ascent_max'] = $value;
                    break;

                case 'athlete':
                    $joins['athlete'] = 'JOIN r.athlete a';
                    $parameters['athlete_name'] = '%'.$value.'%';
                    $parameters['athlete_id'] = $value;

                    if (!empty($criteria['starred'])) {
                        $joins['starred_by_searched'] = 'LEFT JOIN r.starredBy sbs';
                        $selects[] = 'CASE WHEN sbs.id IS NOT NULL 
                                                AND (sbs.name LIKE :athlete_name OR sbs.id = :athlete_id) 
                                           THEN true 
                                           ELSE false END AS starred_by_searched_athlete';
                        $wheres[] = '(a.name LIKE :athlete_name OR a.id = :athlete_id
                                      OR sbs.name LIKE :athlete_name OR sbs.id = :athlete_id)';
                    } else {
                        $wheres[] = '(a.name LIKE :athlete_name OR a.id = :athlete_id)';
                    }
                    break;

                case 'start_dist':
                    // Even if a user removes "start" filter value, "start_latlon" is still kept,
                    // as it is hidden - but we don't want to use it anymore for search.
                    if (!empty($criteria['start_latlon']) && !empty($criteria['start'])) {
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
                    // Even if a user removes "end" filter value, "end_latlon" is still kept,
                    // as it is hidden - but we don't want to use it anymore for search.
                    if (!empty($criteria['end_latlon']) && !empty($criteria['end'])) {
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
        if (empty($orderBy)) {
            $orderBy = ['r.updatedAt' => 'desc'];
        }
        foreach ($orderBy as $field => $direction) {
            if ($field == 'a.name') {
                $joins['athlete'] = 'JOIN r.athlete a';
            }
            $orders[] = $field.' '.$direction;
        }

        // Build query SQL.
        $sqls[] = 'SELECT '.implode(', ', $selects).' FROM App:Route r INDEX BY r.id';
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

    /**
     * Find all routes without segments fetched.
     *
     * @return \App\Entity\Route[]
     */
    public function findAllWithoutSegments()
    {
        return $this->createQueryBuilder('r')
            ->select('r')
            ->where('r.segments IS NULL')
            ->getQuery()
            ->getResult();
    }

    /**
     * Find all not tagged routes.
     *
     * @return \App\Entity\Route[]
     */
    public function findAllNotTagged()
    {
        return $this->createQueryBuilder('r')
            ->select('r')
            ->where('r.tags IS NULL')
            ->getQuery()
            ->getResult();
    }
}
