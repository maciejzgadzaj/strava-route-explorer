<?php

namespace App\Repository;

use App\Entity\Athlete;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * Class AthleteRepository
 *
 * @package App\Repository
 */
class AthleteRepository extends ServiceEntityRepository
{
    /**
     * AthleteRepository constructor.
     *
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Athlete::class);
    }

    /**
     * Find Athlete entities filtered and sorted by a set criteria.
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
        $repository = $this->_em->getRepository(Athlete::class);

        $query = $repository->createQueryBuilder('a', 'a.id')
            ->select('a AS athlete')
            ->addSelect('COUNT(r.id) AS route_count')
            ->addSelect('CASE WHEN a.accessToken IS NOT NULL THEN true ELSE false END AS synchronized')
            ->addSelect('(SELECT COUNT(1) FROM App:Route r2 JOIN r2.starredBy sb WHERE sb.id = a.id) AS starred_count')
            ->leftjoin('App:Route', 'r', 'WITH', 'r.athlete = a.id')
            ->where('r.public = true')
            ->groupBy('a.id');

        // Add sorts.
        if (!empty($orderBy)) {
            foreach ($orderBy as $field => $direction) {
                $query->addOrderBy($field, $direction);
            }
        } else {
            $query->orderBy('synchronized', 'DESC')
                ->addOrderBy('route_count', 'DESC')
                ->addOrderBy('a.name', 'ASC');
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
