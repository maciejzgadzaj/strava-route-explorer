<?php

declare(strict_types=1);

namespace App\Action\Routes;

use App\Service\MapService;
use App\Service\RouteService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/images/routes/{athlete_id}/{route_id}.jpg', name: 'route_map')]
class RouteMap extends AbstractController
{
    public function __invoke(
        int $athlete_id,
        int $route_id,
        RouteService $routeService,
        MapService $mapService,
    ): BinaryFileResponse {
        return $mapService->getMap(
            $routeService->load($route_id),
        );
    }
}
