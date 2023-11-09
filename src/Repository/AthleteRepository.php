<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Athlete;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use function func_get_args;

class AthleteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Athlete::class);
    }

    public function findByFilters(array $criteria, ?array $orderBy = null, int $limit = 50, int $offset = 0): array
    {
        $repository = $this->getEntityManager()->getRepository(Athlete::class);

        $query = $repository->createQueryBuilder('a', 'a.id')
            ->select('a AS athlete')
            ->addSelect('CASE WHEN a.accessToken IS NOT NULL THEN true ELSE false END AS synchronized')
            // Route count.
//            ->addSelect('COUNT(r) AS route_count')
//            ->leftJoin('a.routes', 'r')
//            ->where('r.public = true')
//            ->addSelect('(SELECT COUNT(1) FROM App:Route r2 JOIN r2.starredBy sb WHERE sb.id = a.id) AS starred_count')
            ->groupBy('a.id');

        foreach ($criteria as $key => $value) {
            if (empty($value)) {
                continue;
            }

            switch ($key) {
                case 'name':
                    if (is_numeric(trim($value))) {
                        $query->andWhere('a.id = :athlete_id')->setParameter('athlete_id', $value);
                    } else {
                        $query->andWhere('a.name LIKE :athlete_name')->setParameter('athlete_name', '%'.$value.'%');
                    }
                    break;
            }
        }

        if (!empty($orderBy)) {
            foreach ($orderBy as $field => $direction) {
                $query->addOrderBy($field, $direction);
            }
        } else {
            $query->orderBy('a.lastSync', 'DESC')
//                ->addOrderBy('route_count', 'DESC')
                ->addOrderBy('a.name', 'ASC');
        }

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
}
