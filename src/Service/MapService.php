<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Route;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class MapService
{
    public function __construct(
        private readonly MapQuestService $mapQuestService,
    ) {
    }

    /**
     * Download route map thumbnail from MapQuest and save locally.
     */
    public function getMap(Route $route, bool $force = false): BinaryFileResponse
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
