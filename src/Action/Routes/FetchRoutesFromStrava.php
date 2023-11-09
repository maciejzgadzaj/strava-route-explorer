<?php

declare(strict_types=1);

namespace App\Action\Routes;

use App\Entity\Athlete;
use App\Service\RouteService;
use App\Service\StravaService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/fetch-routes-from-strava', name: 'fetch_routes_from_strava')]
#[IsGranted('ROLE_USER')]
class FetchRoutesFromStrava extends AbstractController
{
    public function __invoke(
        Request $request,
        Security $security,
        StravaService $stravaService,
        RouteService $routeService
    ): Response {
        /** @var Athlete $currentAthlete */
        $currentAthlete = $security->getUser();

        $data = json_decode($request->getContent(), false, 512, JSON_THROW_ON_ERROR);

        $routesData = $stravaService->fetchAthleteRoutes($currentAthlete, $data->page);

        foreach ($routesData as $routeData) {
            $routeService->save($routeData);
        }

        return new JsonResponse([
            'routesFetched' => $routesData->count(),
        ]);
    }
}
