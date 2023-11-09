<?php

declare(strict_types=1);

namespace App\Action\Athletes;

use App\Entity\Athlete;
use App\Form\AddRouteForm;
use App\Form\AthleteSearchForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/athletes', name: 'athletes')]
#[IsGranted('ROLE_USER')]
class ListAthletes extends AbstractController
{
    public function __invoke(Request $request, EntityManagerInterface $entityManager): Response
    {
        $athleteSearchForm = $this->createForm(
            AthleteSearchForm::class,
            [],
            ['csrf_protection' => false],
        );
        $athleteSearchForm->handleRequest($request);

        $filters = [];
        if ($athleteSearchForm->isSubmitted() && $athleteSearchForm->isValid()) {
            if ($athleteSearchForm->get('reset')->isClicked()) {
                return $this->redirectToRoute('athletes');
            }
            $filters = array_filter($athleteSearchForm->getData());
        }

        // Add route.
        $routeAddForm = $this->createForm(AddRouteForm::class);
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
//                'route_count' => 'route_count',
                'last_sync' => 'a.lastSync',
            ];
            $orderBy = $map[$request->query->get('sort')];
            $dir = $request->query->get('dir');

            $order = [$orderBy => $dir];
        }

        // Athletes.
        $athletes = $repository->findByFilters($filters, $order ?? [], $perPage, ($page - 1) * $perPage);

        return $this->render('athletes/list-athletes.html.twig', [
            'data' => $athletes,
            'per_page' => $perPage,
            'pages' => $athletes['pages'],
            'route_add_form' => $routeAddForm->createView(),
            'athlete_search_form' => $athleteSearchForm->createView(),
        ]);
    }
}
