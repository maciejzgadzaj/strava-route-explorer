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

        $this->mapQuestService = $mapQuestService;
        $this->logger = $logger;
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
        }

        return new BinaryFileResponse('images/routes/'.$route->getAthlete()->getId().'/'.$route->getId().'.jpg');
    }
}
