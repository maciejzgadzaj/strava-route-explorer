<?php

namespace App\Controller;

use App\Service\AthleteService;
use App\Service\RouteService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class HomepageController
 *
 * @package App\Controller
 */
class HomepageController extends ControllerBase
{
    /**
     * Home page, obviously.
     *
     * @Route("/", name="homepage")
     *
     * @param \App\Service\RouteService $routeService
     * @param \App\Service\AthleteService $athleteService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(RouteService $routeService, AthleteService $athleteService)
    {
        // @TODO: Remove in some time.
        $athleteService->removeOldCookies();

        return $this->render(
            'homepage/index.html.twig',
            [
                'current_athlete' => $athleteService->getCurrentAthlete(),
                'route_count' => $routeService->count(),
                'athlete_count' => $athleteService->count(),
            ]
        );
    }
}
