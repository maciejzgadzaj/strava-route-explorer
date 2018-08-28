<?php

namespace App\Service;

use App\Entity\Athlete;
use App\Entity\Route;
use App\Exception\NoticeException;
use CrEOF\Spatial\PHP\Types\Geometry\Point;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Exception\ClientException;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Class RouteService
 *
 * @package App\Service
 */
class RouteService extends EntityService
{
    /**
     * @var \App\Repository\RouteRepository
     */
    private $repository;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \App\Service\AthleteService
     */
    private $athleteService;

    /**
     * @var \App\Service\MapService
     */
    private $mapService;

    /**
     * @var \App\Service\StravaService
     */
    private $stravaService;

    /**
     * RouteService constructor.
     *
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
     * @param \Psr\Log\LoggerInterface $logger
     * @param \App\Service\AthleteService $athleteService
     * @param \App\Service\MapService $mapService
     * @param \App\Service\StravaService $stravaService
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        SessionInterface $session,
        LoggerInterface $logger,
        AthleteService $athleteService,
        MapService $mapService,
        StravaService $stravaService
    ) {
        parent::__construct($entityManager, $session);

        $this->repository = $this->entityManager->getRepository(Route::class);
        $this->logger = $logger;
        $this->athleteService = $athleteService;
        $this->mapService = $mapService;
        $this->stravaService = $stravaService;
    }

    /**
     * Return total number of routes.
     *
     * @return int
     */
    public function count()
    {
        return $this->repository->count([]);
    }

    /**
     * Check if route exists.
     *
     * @param int $routeId
     *
     * @return bool
     */
    public function exists($routeId)
    {
        return !empty($this->repository->findOneBy(['id' => $routeId]));
    }

    /**
     * Load a route.
     *
     * @param int $routeId
     *
     * @return \App\Entity\Route
     */
    public function load($routeId)
    {
        return $this->repository->findOneBy(['id' => $routeId]);
    }

    /**
     * Save new or update existing route.
     *
     * @param object $routeData
     *
     * @return \App\Entity\Route
     */
    public function save($routeData)
    {
        if (!$route = $this->repository->find($routeData->id)) {
            $route = new Route();
            $route->setId($routeData->id);
        }

        $athlete = $this->entityManager->getRepository(Athlete::class)->find($routeData->athlete->id);
        $route->setAthlete($athlete);

        $route->setType($routeData->type);
        $route->setSubType($routeData->sub_type);
        $route->setName(trim($routeData->name));
        $route->setDescription($routeData->description);
        $route->setDistance($routeData->distance);
        $route->setElevationGain($routeData->elevation_gain);
        $route->setPublic($routeData->public ?? true);
        $route->setCreatedAt(\DateTime::createFromFormat('Y-m-d\TH:i:s\Z', $routeData->created_at));
        $route->setUpdatedAt(\DateTime::createFromFormat('Y-m-d\TH:i:s\Z', $routeData->updated_at));

        // No segments are included in getRoutesByAthleteId,
        // they are added only to the getRouteById.
        $climbCategory = 0;
        if (!empty($routeData->segments)) {
            foreach ($routeData->segments as $segment) {
                if ($segment->climb_category > $climbCategory) {
                    $climbCategory = $segment->climb_category;
                }
            }
        }
        $route->setClimbCategory($climbCategory);

        if (!empty($routeData->map->summary_polyline)) {
            $route->setPolylineSummary($routeData->map->summary_polyline);

            $list = \Polyline::decode($routeData->map->summary_polyline);
            $pairs = \Polyline::pair($list);

            $start = reset($pairs);
            $startPoint = new Point($start[0], $start[1]);
            $route->setStart($startPoint);

            $end = end($pairs);
            $endPoint = new Point($end[0], $end[1]);
            $route->setEnd($endPoint);
        }

        $this->entityManager->persist($route);
        $this->entityManager->flush();

        return $route;
    }

    /**
     * Delete a route.
     *
     * @param int $routeId
     */
    public function delete($routeId)
    {
        $route = $this->repository->findOneBy(['id' => $routeId]);

        $this->entityManager->remove($route);
        $this->entityManager->flush();
    }

    public function syncRoute($routeId)
    {
        try {
            $content = $this->stravaService->getRoute($routeId);

            if (!empty($content->private)) {
                // If a private route is found in our database, let's delete it.
                if ($this->exists($content->id)) {
                    $this->delete($content->id);

                    throw new NoticeException(
                        strtr(
                            'Deleted private route "%route_name%" (%route_id%) by %athlete%.',
                            [
                                '%route_name%' => $content->name,
                                '%route_id%' => $content->id,
                                '%athlete%' => $content->athlete->firstname.' '.$content->athlete->lastname,
                            ]
                        )
                    );
                }

                throw new \Exception('Cowardly refusing to add a private route.');
            }

            if (!$this->athleteService->exists($content->athlete->id)) {
                $this->athleteService->save($content->athlete);
            }

            // Save route.
            $route = $this->save($content);

            $message = '%action% route "%route_name%" (%route_id%) by %athlete%.';
            $params = [
                '%action%' => $route->isNew() ? 'Added' : 'Updated',
                '%route_name%' => $content->name,
                '%route_id%' => $content->id,
                '%athlete%' => $content->athlete->firstname.' '.$content->athlete->lastname,
            ];
            $this->logger->info(strtr($message, $params));
            $this->session->getFlashBag()->add('notice', strtr($message, $params));

            return $route;
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $content = \GuzzleHttp\json_decode($response->getBody()->getContents());
            $this->logger->error($content->message);
            $this->session->getFlashBag()->add('error', $content->message);

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
                $this->session->getFlashBag()->add('notice', strtr($message, $params));
            }
        } catch (NoticeException $e) {
            $this->logger->warning($e->getMessage());
            $this->session->getFlashBag()->add('notice', $e->getMessage());
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $this->session->getFlashBag()->add('error', $e->getMessage());
        }
    }

    /**
     * Return array of athlete routes keyed by route ID.
     *
     * @param \App\Entity\Athlete $athlete
     *
     * @return array
     */
    public function getAthleteRoutes(Athlete $athlete)
    {
        /** @var \App\Entity\Route[] $routes */
        $routes = $this->repository->findBy(['athlete' => $athlete->getId()]);

        $return = [];
        foreach ($routes as $route) {
            $return[$route->getId()] = $route;
        }

        return $return;
    }

    /**
     * Return array of athlete routes keyed by route ID.
     *
     * @param \App\Entity\Athlete $athlete
     *
     * @return array
     */
    public function getAthleteStarredRoutes(Athlete $athlete)
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

    /**
     * Delete athlete routes excluding specified ids.
     *
     * @param \App\Entity\Athlete $athlete
     * @param array $excludeIds
     */
    public function deleteAthleteRoutes(Athlete $athlete, array $excludeIds)
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

    /**
     * Unstar athlete starred routes excluding specified ids.
     *
     * @param \App\Entity\Athlete $athlete
     * @param array $excludeIds
     *
     * @return int
     */
    public function unstarAthleteRoutes(Athlete $athlete, array $excludeIds)
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

    public function getRouteTypes($type = null)
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
        ];

        if (isset($type)) {
            return $types[$type] ?? null;
        }

        return $types;
    }

    public function getLocationDistances($distance = null)
    {
        $distances = [
            '0.1' => '100 m',
            '0.5' => '500 m',
            '1' => '1 km',
            '5' => '5 km',
            '10' => '10 km',
            '20' => '20 km',
            '50' => '50 km',
            '100' => '100 km',
        ];

        if (isset($distance)) {
            return $distances[$distance] ?? null;
        }

        return $distances;
    }

    public function getFiltersForDisplay($filters)
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
                'label' => 'min ascent',
                'suffix' => ' m',
            ],
            'elevation_gain_max' => [
                'label' => 'max ascent',
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
                $return[$nameMap[$key]['label']] = $value . ($nameMap[$key]['suffix'] ?? '');
            }
        }

        return $return;
    }
}
