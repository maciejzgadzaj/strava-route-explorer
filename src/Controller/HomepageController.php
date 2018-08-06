<?php

namespace App\Controller;

use App\Service\RouteService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class HomepageController
 *
 * @package App\Controller
 */
class HomepageController extends Controller
{
    /**
     * Home page, obviously.
     *
     * @Route("/", name="homepage")
     *
     * @param \App\Service\RouteService $routeService
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(RouteService $routeService)
    {
        return $this->render(
            'homepage/index.html.twig',
            [
                'count' => $routeService->count(),
            ]
        );
    }
}
