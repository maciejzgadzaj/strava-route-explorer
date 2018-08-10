<?php

namespace App\Service;

use App\Entity\Athlete;
use App\Entity\Route;
use CrEOF\Spatial\PHP\Types\Geometry\Point;

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
     * @var \App\Service\MapService
     */
    private $mapService;

    /**
     * RouteService constructor.
     *
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
     * @param \App\Service\MapService $mapService
     */
    public function __construct(
        \Doctrine\ORM\EntityManagerInterface $entityManager,
        \Symfony\Component\HttpFoundation\Session\SessionInterface $session,
        MapService $mapService
    ) {
        parent::__construct($entityManager, $session);

        $this->repository = $this->entityManager->getRepository(Route::class);
        $this->mapService = $mapService;
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
}
