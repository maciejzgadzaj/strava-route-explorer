<?php

namespace App\Repository;

use App\Entity\Route;
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
        $queryBuilder = $this
            ->getEntityManager()
            ->createQueryBuilder();

        $query = $queryBuilder->select(['r'])
            ->from('App:Route', 'r');

        foreach ($criteria as $key => $value) {
            if (empty($value)) {
                continue;
            }

            switch ($key) {
                case 'type':
                    $query->andWhere($queryBuilder->expr()->eq('r.type', $value));
                    break;
                case 'name':
                    $query->andWhere(
                        $queryBuilder->expr()->orX(
                            $queryBuilder->expr()->like('r.name', '?1'),
                            $queryBuilder->expr()->eq('r.id', '?2')
                        )
                    );
                    $query->setParameter(1, '%'.$value.'%');
                    $query->setParameter(2, $value);
                    break;
                case 'distance_min':
                    $query->andWhere($queryBuilder->expr()->gt('r.distance', $value * 1000));
                    break;
                case 'distance_max':
                    $query->andWhere($queryBuilder->expr()->lt('r.distance', $value * 1000));
                    break;
                case 'elevation_gain_min':
                    $query->andWhere($queryBuilder->expr()->gt('r.elevationGain', $value));
                    break;
                case 'elevation_gain_max':
                    $query->andWhere($queryBuilder->expr()->lt('r.elevationGain', $value));
                    break;
                case 'athlete':
                    $query->join('r.athlete', 'a');
                    $query->andWhere(
                        $queryBuilder->expr()->orX(
                            $queryBuilder->expr()->like('a.name', '?3'),
                            $queryBuilder->expr()->eq('a.id', '?4')
                        )
                    );
                    $query->setParameter(3, '%'.$value.'%');
                    $query->setParameter(4, $value);
                    break;
            }
        }

        foreach ($orderBy as $field => $direction) {
            if ($field == 'a.name') {
                $query->join('r.athlete', 'a');
            }
            $query->orderBy($field, $direction);
        }

        $paginator = new Paginator($query->getQuery());
        $totalResults = count($paginator);

        return [
            'total' => count($paginator),
            'pages' => ceil($totalResults / $limit),
            'results' => $paginator
                ->getQuery()
                ->setFirstResult($offset)
                ->setMaxResults($limit)
                ->getResult(),
        ];
    }
}
