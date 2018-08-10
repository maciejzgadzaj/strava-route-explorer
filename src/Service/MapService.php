<?php

namespace App\Service;

use App\Entity\Athlete;
use App\Entity\Map;
use App\Entity\Route;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Class MapService
 *
 * @package App\Service
 */
class MapService extends EntityService
{
    /**
     * @var \App\Repository\MapRepository
     */
    private $repository;

    /**
     * @var \App\Service\MapQuestService
     */
    private $mapQuestService;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * MapService constructor.
     *
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
     * @param \App\Service\MapQuestService $mapQuestService
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Doctrine\ORM\EntityManagerInterface $entityManager,
        \Symfony\Component\HttpFoundation\Session\SessionInterface $session,
        MapQuestService $mapQuestService,
        LoggerInterface $logger
    ) {
        parent::__construct($entityManager, $session);

        $this->repository = $this->entityManager->getRepository(Map::class);
        $this->mapQuestService = $mapQuestService;
        $this->logger = $logger;
    }

    /**
     * Load a map.
     *
     * @param int $id
     *
     * @return \App\Entity\Map
     */
    public function load($id)
    {
        return $this->repository->findOneBy(['id' => $id]);
    }

    /**
     * Load a map by route id.
     *
     * @param int $routeId
     *
     * @return \App\Entity\Map
     */
    public function loadByRouteId($routeId)
    {
        return $this->repository->findOneBy(['route' => $routeId]);
    }

    /**
     * Save new or update existing map.
     *
     * @param object $mapData
     *
     * @return \App\Entity\Map
     */
    public function save($mapData)
    {
        if (!$map = $this->repository->find($mapData->id)) {
            $map = new Map();
            $map->setId($mapData->id);
        } else {
            // If we're updating an existing map, keep a copy of the old version
            // for compation, to see if we need to regenerate the map thumbnail.
            /** @var \App\Entity\Map $oldMap */
            $oldMap = clone $map;
        }

        $routeId = preg_replace('/[A-Za-z]+/', '', $mapData->id);
        /** @var \App\Entity\Route $route */
        $route = $this->entityManager->getRepository(Route::class)->find($routeId);
        $map->setRoute($route);

        $map->setPolylineSummary($mapData->summary_polyline);

        $list = \Polyline::decode($mapData->summary_polyline);
        $pairs = \Polyline::pair($list);

        $start = reset($pairs);
        $map->setStartLatitude($start[0]);
        $map->setStartLongitude($start[1]);

        $end = end($pairs);
        $map->setEndLatitude($end[0]);
        $map->setEndLongitude($end[1]);

        $this->entityManager->persist($map);
        $this->entityManager->flush();

        // Regenerate route map thumbnail if polyline has changed.
        if (!$map->isNew() && $oldMap->getPolylineSummary() != $map->getPolylineSummary()) {
            $force = true;
        }

        // Generate route map thumbnail if it doesn't exist yet, or if forced.
        $this->getMap($map, $force ?? false);

        return $map;
    }

    /**
     * Delete a map.
     *
     * @param int $routeId
     */
    public function delete($routeId)
    {
        $map = $this->repository->findOneBy(['id' => $routeId]);

        $this->entityManager->remove($map);
        $this->entityManager->flush();
    }

    /**
     * Download route map thumbnail from MapQuest and save locally.
     *
     * @param \App\Entity\Map $map
     * @param bool $force
     *
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function getMap(Route $route, $force = false)
    {
        $fileSystem = new Filesystem();

        $dir = 'images/routes/'.$route->getAthlete()->getId();
        $path = $dir.'/'.$route->getId().'.jpg';

        if (!$fileSystem->exists($path) || $force) {
            $content = $this->mapQuestService->getStaticMapWithPolyline($route->getPolylineSummary());

            if (!$fileSystem->exists($dir)) {
                $fileSystem->mkdir($dir);
            }

            $fileSystem->dumpFile($path, $content);

            $this->logger->debug('Fetched map thumbnail from MapQuest for route '.$route->getId());
        }

        return new BinaryFileResponse('images/routes/'.$route->getAthlete()->getId().'/'.$route->getId().'.jpg');
    }
}
