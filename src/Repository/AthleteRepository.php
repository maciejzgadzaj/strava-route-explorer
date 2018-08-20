<?php

namespace App\Repository;

use App\Entity\Athlete;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
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
     * @param \Symfony\Bridge\Doctrine\RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
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
