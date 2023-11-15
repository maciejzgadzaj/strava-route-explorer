<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Athlete;
use App\Entity\Route;
use App\Repository\AthleteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use LongitudeOne\Spatial\PHP\Types\Geometry\Point;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\Exception\ClientException;

class RouteService
{
    private AthleteRepository|EntityRepository $repository;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly FlashService $flashService,
        private readonly LoggerInterface $logger,
        private readonly AthleteService $athleteService,
        private readonly StravaService $stravaService
    ) {
        $this->repository = $this->entityManager->getRepository(Route::class);
    }

    public function count(): int
    {
        return $this->repository->count([]);
    }

    public function exists(int $routeId): bool
    {
        return !empty($this->repository->findOneBy(['id' => $routeId]));
    }

    public function load(int $routeId): Route
    {
        return $this->repository->findOneBy(['id' => $routeId]);
    }

    public function save(array $routeData): Route
    {
        /** @var Route $route */
        if (!$route = $this->repository->find($routeData['id'])) {
            $route = new Route();
            $route->setId($routeData['id']);
        }

        // Find local athlete entity for the route.
        if (!$athlete = $this->entityManager->getRepository(Athlete::class)->find($routeData['athlete']['id'])) {
            // If the athlete entity does not exist yet (which might be the
            // case when synchronizing a route starred by current athlete,
            // but created by a different one) create new athlete entity.
            $athlete = $this->athleteService->save($routeData['athlete']);
        }

        $route
            ->setAthlete($athlete)
            ->setType($routeData['type'])
            ->setSubType($routeData['sub_type'])
            ->setName(trim($routeData['name']))
            ->setDescription($routeData['description'] ?? '')
            ->setPrivate($routeData['private'])
            ->setDistance($routeData['distance'])
            ->setElevationGain($routeData['elevation_gain'])
            ->setPublic($route->isNew() ? !$routeData['private'] : $route->isPublic())
            ->setCreatedAt(\DateTime::createFromFormat('Y-m-d\TH:i:s\Z', $routeData['created_at']))
            ->setUpdatedAt(\DateTime::createFromFormat('Y-m-d\TH:i:s\Z', $routeData['updated_at']))
            ->setMapUrl($routeData['map_urls']['url'])
        ;

        // No segments are included in getRoutesByAthleteId,
        // they are added only to the getRouteById.
        $climbCategory = 0;
        if (!empty($routeData['segments'])) {
            $segments = array_combine(
                array_column($routeData['segments'], 'id'),
                array_column($routeData['segments'], 'name'),
            );
            $route->setSegments($segments);

            foreach ($routeData['segments'] as $segment) {
                if ($segment['climb_category'] > $climbCategory) {
                    $climbCategory = $segment['climb_category'];
                }
            }
        }
        $route->setClimbCategory($climbCategory);

        if (!empty($routeData['map']['summary_polyline'])) {
            $route->setPolylineSummary($routeData['map']['summary_polyline']);

            $list = \Polyline::decode($routeData['map']['summary_polyline']);
            $pairs = \Polyline::pair($list);

            $start = reset($pairs);
            $startPoint = new Point($start[1], $start[0]);
            $route->setStart($startPoint);

            $end = end($pairs);
            $endPoint = new Point($end[1], $end[0]);
            $route->setEnd($endPoint);
        }

        $this->entityManager->persist($route);
        $this->entityManager->flush();

        return $route;
    }

    public function delete(int $routeId): void
    {
        $route = $this->repository->findOneBy(['id' => $routeId]);

        $this->entityManager->remove($route);
        $this->entityManager->flush();
    }

    public function syncRoute(int $routeId): ?Route
    {
        try {
            $response = $this->stravaService->getRoute($routeId);

            $route = $this->save($response);

            $message = '%action% route "%route_name%" (%route_id%) by %athlete%.';
            $params = [
                '%action%' => $route->isNew() ? 'Added' : 'Updated',
                '%route_name%' => $route->getName(),
                '%route_id%' => $route->getId(),
                '%athlete%' => $route->getAthlete()->getName(),
            ];
            $this->logger->info(strtr($message, $params));
            $this->flashService->add('notice', strtr($message, $params));

            return $route;
        } catch (ClientException $clientException) {
            $response = $clientException->getResponse()->toArray();
            $this->logger->error($response->message);
            $this->flashService->add('error', $response->message);

            // Delete local route if it was not found on Strava.
            if ($localRoute = $this->load($routeId)) {
                $this->delete($localRoute->getId());

                $message = 'Deleted route "%route_name%" (%route_id%) by %athlete% not found on Strava.';
                $params = [
                    '%route_name%' => $localRoute->getName(),
                    '%route_id%' => $localRoute->getId(),
                    '%athlete%' => $localRoute->getAthlete()->getName(),
                ];
                $this->logger->info(strtr($message, $params));
                $this->flashService->add('notice', strtr($message, $params));
            }
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
            $this->flashService->add('error', $exception->getMessage());
        }

        return null;
    }

    /**
     * @return array<int, Route>
     */
    public function getAthleteRoutes(Athlete $athlete): array
    {
        /** @var \App\Entity\Route[] $routes */
        $routes = $this->repository->findBy(
            ['athlete' => $athlete->getId()],
            ['updatedAt' => 'DESC'],
        );

        $return = [];
        foreach ($routes as $route) {
            $return[$route->getId()] = $route;
        }

        return $return;
    }

    /**
     * @return array<int, Route>
     */
    public function getAthleteStarredRoutes(Athlete $athlete): array
    {
        $qb = $this->repository->createQueryBuilder('r');

        /** @var \App\Entity\Route[] $routes */
        $routes = $qb->join('r.starredBy', 'a')
            ->where($qb->expr()->eq('a.id', $athlete->getId()))
            ->getQuery()
            ->getResult();

        $return = [];
        foreach ($routes as $route) {
            $return[$route->getId()] = $route;
        }

        return $return;
    }

    public function deleteAthleteRoutes(Athlete $athlete, array $excludeIds): int
    {
        $qb = $this->repository->createQueryBuilder('r');

        if (!empty($excludeIds)) {
            $where = $qb->expr()->andX(
                $qb->expr()->eq('r.athlete', $athlete->getId()),
                $qb->expr()->notIn('r.id', $excludeIds)
            );
        } else {
            $where = $qb->expr()->eq('r.athlete', $athlete->getId());
        }

        $routesToDelete = $qb
            ->where($where)
            ->getQuery()
            ->getResult();

        if (!empty($routesToDelete)) {
            foreach ($routesToDelete as $routeToDelete) {
                $this->entityManager->remove($routeToDelete);
            }
        }

        return count($routesToDelete);
    }

    public function unstarAthleteRoutes(Athlete $athlete, array $excludeIds): int
    {
        $unstarred = 0;

        $builder = $this->repository->createQueryBuilder('r')
            ->join('r.starredBy', 'a', 'WITH', 'a.id = :athlete_id')
            ->setParameter('athlete_id', $athlete->getId());

        /** @var \App\Entity\Route[] $starredRoutes */
        $starredRoutes = $builder->getQuery()->getResult();

        foreach ($starredRoutes as $starredRoute) {
            if (!in_array($starredRoute->getId(), $excludeIds)) {
                $starredRoute->removeStarredBy($athlete);
                $unstarred++;
            }
        }

        return $unstarred;
    }

    /**
     * @return array<string, string>|string|null
     */
    public function getRouteTypes(string $type = null): array|string|null
    {
        $types = [
            '1' => 'bike',
            '1,1' => 'bike · road',
            '1,2' => 'bike · mtb',
            '1,3' => 'bike · cross',
            '1,5' => 'bike · mixed',
            '2' => 'run',
            '2,1' => 'run · road',
            '2,4' => 'run · trail',
            '2,5' => 'run · mixed',
            '3' => 'hike',
        ];

        if (isset($type)) {
            return $types[$type] ?? null;
        }

        return $types;
    }

    /**
     * @return array<string, string>|string|null
     */
    public function getLocationDistances($distance = null): array|string|null
    {
        $distances = [
            null => 'any',
            '100' => '100 m',
            '500' => '500 m',
            '1000' => '1 km',
            '5000' => '5 km',
            '10000' => '10 km',
            '20000' => '20 km',
            '50000' => '50 km',
            '100000' => '100 km',
        ];

        if (isset($distance)) {
            return $distances[$distance] ?? null;
        }

        return $distances;
    }

    /**
     * @param  array  $filters
     * @return array<string, array<string, string>>
     */
    public function getFiltersForDisplay(array $filters): array
    {
        $return = [];

        $nameMap = [
            'type' => [
                'label' => 'type',
                'values' => $this->getRouteTypes(),
            ],
            'name' => [
                'label' => 'name',
            ],
            'distance_min' => [
                'label' => 'min distance',
                'suffix' => ' km',
            ],
            'distance_max' => [
                'label' => 'max distance',
                'suffix' => ' km',
            ],
            'elevation_gain_min' => [
                'label' => 'min elevation gain',
                'suffix' => ' m',
            ],
            'elevation_gain_max' => [
                'label' => 'max elevation gain',
                'suffix' => ' m',
            ],
            'athlete' => [
                'label' => 'athlete',
            ],
            'start' => [
                'label' => 'start',
            ],
            'end' => [
                'label' => 'end',
            ],
        ];

        foreach ($filters as $key => $value) {
            if (in_array($key, array_keys($nameMap))) {
                if (!empty($nameMap[$key]['values'][$value])) {
                    $value = $nameMap[$key]['values'][$value];
                }
                if ($key == 'type') {
                    $value = preg_replace('/(bike · |run · )/', '', $value);
                }
                if ($key == 'start') {
                    $value_parts = explode(', ', $value);
                    $value = $value_parts[0];
                    if (!empty($filters['start_dist'])) {
                        $value = 'within '.($this->getLocationDistances($filters['start_dist']) ? $this->getLocationDistances($filters['start_dist']) : $filters['start_dist']).' from '.$value;
                    }
                }
                if ($key == 'end') {
                    $value_parts = explode(', ', $value);
                    $value = $value_parts[0];
                    if (!empty($filters['end_dist'])) {
                        $value = 'within '.($this->getLocationDistances($filters['end_dist']) ? $this->getLocationDistances($filters['end_dist']) : $filters['end_dist']).' from '.$value;
                    }
                }
                $return[$nameMap[$key]['label']] = $value.($nameMap[$key]['suffix'] ?? '');
            }
        }

        return $return;
    }
}
