<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Athlete;
use App\Entity\Route;
use CrEOF\Spatial\PHP\Types\Geometry\Point;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\SecurityBundle\Security;

class RouteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private readonly Security $security)
    {
        parent::__construct($registry, Route::class);
    }

    public function findByFilters(array $criteria, ?array $orderBy = [], int $limit = 50, int $offset = 0): array
    {
        /** @var ?Athlete $currentAthlete */
        $currentAthlete = $this->security->getUser();

        $repository = $this->getEntityManager()->getRepository(Route::class);

        $query = $repository->createQueryBuilder('r', 'r.id')
            ->select('r AS route')
        ;

        // Show public routes to everyone, and private ones only to their owners.
        if ($currentAthlete) {
            $query
                ->andWhere('(r.public = TRUE OR r.athlete = :me)')
                ->setParameter('me', $currentAthlete->getId())
            ;
        } else {
            $query->andWhere('r.public = TRUE');
        }

        foreach ($criteria as $key => $value) {
            if (empty($value)) {
                continue;
            }

            switch ($key) {
                case 'type':
                    $values = explode(',', $value);
                    $query
                        ->andWhere('r.type = :type')
                        ->setParameter('type', $values[0])
                    ;
                    if (!empty($values[1])) {
                        $query
                            ->andWhere('r.subType = :sub_type')
                            ->setParameter('sub_type', $values[1])
                        ;
                    }
                    break;

                case 'name':
                    $query
                        ->addSelect('MATCH (r.name) AGAINST (:name BOOLEAN) AS name_score')
                        ->addSelect('MATCH (r.description) AGAINST (:name BOOLEAN) AS description_score')
                        ->andWhere('(r.id = :id OR MATCH (r.name, r.description) AGAINST (:name BOOLEAN) > 0)')
                        ->setParameter('id', $value)
                        ->setParameter('name', $value)
                        ->addOrderBy('name_score * 100 + description_score', 'DESC')
                    ;
                    break;

                case 'segments':
                    $query
                        ->addSelect('MATCH (r.segments) AGAINST (:segment_name BOOLEAN) AS segments_score')
                        ->andWhere('MATCH (r.segments) AGAINST (:segment_name BOOLEAN) > 0')
                        ->setParameter('segment_name', $value)
                        ->addOrderBy('segments_score', 'DESC')
                    ;
                    break;

                case 'tags':
                    $query
                        ->addSelect('MATCH (r.tags) AGAINST (:tag_name BOOLEAN) AS tags_score')
                        ->andWhere('MATCH (r.tags) AGAINST (:tag_name BOOLEAN) > 0')
                        ->setParameter('tag_name', $value)
                        ->addOrderBy('tags_score', 'DESC')
                    ;
                    break;

                case 'distance_min':
                    $query
                        ->andWhere('r.distance > :min_distance')
                        ->setParameter('min_distance', $value * 1000)
                    ;
                    break;

                case 'distance_max':
                    $query
                        ->andWhere('r.distance < :max_distance')
                        ->setParameter('max_distance', $value * 1000)
                    ;
                    break;

                case 'elevation_gain_min':
                    $query
                        ->andWhere('r.elevationGain > :elevation_gain_min')
                        ->setParameter('elevation_gain_min', $value)
                    ;
                    break;

                case 'elevation_gain_max':
                    $query
                        ->andWhere('r.elevationGain < :elevation_gain_max')
                        ->setParameter('elevation_gain_max', $value)
                    ;
                    break;

                case 'athlete':
                    $query->join('r.athlete', 'a');
                    // We need this distinction, as searching for "athlete_id" = <string>
                    // will match unknown Strava athlete with id = 0.
                    if (is_numeric(trim($value))) {
                        $query->setParameter('athlete_id', $value);
                    }
                    else {
                        $query->setParameter('athlete_name', '%'.$value.'%');
                    }

                    if (!empty($criteria['starred'])) {
                        $query->leftJoin('r.starredBy', 'rsb');
                        if (is_numeric(trim($value))) {
                            $query
                                ->addSelect('
                                    CASE WHEN rsb.id IS NOT NULL AND (rsb.id = :athlete_id)
                                         THEN true
                                         ELSE false
                                    END AS starred_by_searched_athlete
                                ')
                                ->andWhere('(rsb.id = :athlete_id)')
                            ;
                        } else {
                            $query
                                ->addSelect('
                                    CASE WHEN rsb.id IS NOT NULL AND (rsb.name LIKE :athlete_name)
                                         THEN true
                                         ELSE false
                                    END AS starred_by_searched_athlete
                                ')
                                ->andWhere('(rsb.name LIKE :athlete_name)')
                            ;
                        }
                    } else {
                        if (is_numeric(trim($value))) {
                            $query->andWhere('(a.id = :athlete_id)');
                        } else {
                            $query->andWhere('(a.name LIKE :athlete_name)');
                        }
                    }

                    if (!empty($criteria['private'])) {
                        $query->andWhere('r.public = 0');
                    }

                    break;

                // https://www.movable-type.co.uk/scripts/latlong.html
                // https://www.movable-type.co.uk/scripts/latlong-db.html
                case 'start':
                    if (!empty($criteria['start_latlon'])) {
                        $start = new Point(array_reverse(explode(',', $criteria['start_latlon'])));
                        $query
                            ->addSelect('
                                ( 3959 * acos(cos(radians('.$start->getLatitude().'))
                                * cos( radians( x(r.start) ) )
                                * cos( radians( y(r.start) )
                                - radians('.$start->getLongitude().') )
                                + sin( radians('.$start->getLatitude().') )
                                * sin( radians( x(r.start) ) ) ) ) AS start_dist
                            ')
                            ->addOrderBy('start_dist', 'ASC')
                        ;
                    }
                    break;

                case 'start_dist':
                    // Even if a user removes "start" filter value, "start_latlon" is still kept,
                    // as it is hidden - but we don't want to use it anymore for search.
                    if (!empty($criteria['start_latlon']) && !empty($criteria['start'])) {
                        $query
                            ->andHaving('start_dist < :start_dist')
                            ->setParameter('start_dist', $value)
                            ->addOrderBy('start_dist', 'ASC')
                        ;
                    }
                    break;

                case 'end':
                    if (!empty($criteria['end_latlon'])) {
                        $end = new Point(array_reverse(explode(',', $criteria['end_latlon'])));
                        $query
                            ->addSelect('
                                ( 3959 * acos(cos(radians('.$end->getLatitude().'))
                                * cos( radians( x(r.end) ) )
                                * cos( radians( y(r.end) )
                                - radians('.$end->getLongitude().') )
                                + sin( radians('.$end->getLatitude().') )
                                * sin( radians( x(r.end) ) ) ) ) AS end_dist
                            ')
                            ->addOrderBy('end_dist', 'ASC')
                        ;
                    }
                    break;

                case 'end_dist':
                    // Even if a user removes "end" filter value, "end_latlon" is still kept,
                    // as it is hidden - but we don't want to use it anymore for search.
                    if (!empty($criteria['end_latlon']) && !empty($criteria['end'])) {
                        $query
                            ->andHaving('end_dist < :end_dist')
                            ->setParameter('end_dist', $value)
                            ->addOrderBy('end_dist', 'ASC')
                        ;
                    }
                    break;
            }
        }

        // If no order was added by any of the filters above, let's add a default one.
        $query->addOrderBy('r.updatedAt', 'DESC');

        $paginator = new Paginator($query);

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

    public function findAllWithoutSegments(): iterable
    {
        return $this->createQueryBuilder('r')
            ->select('r.id')
            ->where('r.segments IS NULL')
            ->getQuery()
            ->toIterable()
        ;
    }

    public function findAllNotTagged(int $athleteId = null): iterable
    {
        $builder = $this->createQueryBuilder('r')
            ->select('r.id')
            ->andWhere('r.tags IS NULL')
            ->orderBy('r.updatedAt', 'DESC')
        ;

        if ($athleteId) {
            $builder->andWhere('r.athlete = :athlete_id');
            $builder->setParameter('athlete_id', $athleteId);
        }

        return $builder->getQuery()->toIterable();
    }
}
