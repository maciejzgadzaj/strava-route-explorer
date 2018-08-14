<?php

namespace App\Controller;

use App\Entity\Athlete;
use App\Form\RouteAddType;
use App\Service\AthleteService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class AthletesController
 *
 * @package App\Controller
 */
class AthletesController extends ControllerBase
{
    /**
     * List athletes.
     *
     * @Route("/athletes", name="athletes")
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param \App\Service\AthleteService $athleteService
     *
     * @return \Symfony\Component\HttpFoundation\Response|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function listAction(Request $request, EntityManagerInterface $entityManager, AthleteService $athleteService)
    {
        // Allow access only to athletes authorized with Strava.
        if (!$athleteService->isAuthorized()) {
            return $this->redirectToRoute('strava_auth');
        }

        // Add route.
        $routeAddForm = $this->createForm(RouteAddType::class);
        $routeAddForm->handleRequest($request);

        if ($routeAddForm->isSubmitted() && $routeAddForm->isValid()) {
            $routeId = str_replace('https://www.strava.com/routes/', '', $routeAddForm->getData()['route_id']);
            $routeId = preg_replace('/[^0-9]/', '', $routeId);

            return $this->redirectToRoute('routes_add', ['route_id' => $routeId]);
        }

        /** @var \App\Repository\AthleteRepository $repository */
        $repository = $entityManager->getRepository(Athlete::class);

        // Paging.
        $perPage = $request->query->get('per_page', 50);
        $page = $request->query->get('page', 1);

        // Sorting.
        if ($sort = $request->query->get('sort')) {
            $map = [
                'name' => 'a.name',
                'route_count' => 'route_count',
                'last_sync' => 'a.lastSync',
            ];
            $orderBy = $map[$request->query->get('sort')];
            $dir = $request->query->get('dir');

            $order = [$orderBy => $dir];
        }

        // Athletes.
        $athletes = $repository->findByFilters([], $order ?? [], $perPage, ($page - 1) * $perPage);

        return $this->render(
            'athletes/list.html.twig',
            [
                'current_athlete' => $athleteService->getCurrentAthlete(),
                'data' => $athletes,
                'per_page' => $perPage,
                'pages' => $athletes['pages'],
                'route_add_form' => $routeAddForm->createView(),
            ]
        );
    }
}
