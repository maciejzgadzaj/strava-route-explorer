<?php

declare(strict_types=1);

namespace App\Action\Routes;

use App\Entity\Route as StravaRoute;
use App\Form\AddRouteForm;
use App\Form\RouteSearchForm;
use App\Service\RouteService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/routes', name: 'routes')]
#[IsGranted('ROLE_USER')]
class ListRoutes extends AbstractController
{
    public function __invoke(
        Request $request,
        EntityManagerInterface $entityManager,
        RouteService $routeService,
    ): Response|RedirectResponse {
        $addRouteForm = $this->createForm(AddRouteForm::class);
        $addRouteForm->handleRequest($request);

        if ($addRouteForm->isSubmitted() && $addRouteForm->isValid()) {
            $routeId = str_replace('https://www.strava.com/routes/', '', $addRouteForm->getData()['route_id']);
            $routeId = preg_replace('/[^0-9]/', '', $routeId);

            return $this->redirectToRoute('routes_add', ['route_id' => $routeId]);
        }

        // Filters.
        $routeFilterForm = $this->createForm(
            RouteSearchForm::class,
            ['route_service' => $routeService],
            ['csrf_protection' => false],
        );
        $routeFilterForm->handleRequest($request);

        $filters = [];
        if ($routeFilterForm->isSubmitted() && $routeFilterForm->isValid()) {
            if ($routeFilterForm->get('reset')->isClicked()) {
                return $this->redirectToRoute('routes');
            }
            $filters = array_filter($routeFilterForm->getData());
        }
        /** @var \App\Repository\RouteRepository $repository */
        $repository = $entityManager->getRepository(StravaRoute::class);

        // Paging.
        $perPage = $request->query->get('per_page', 50);
        $page = $request->query->get('page', 1);

        // Sorting.
        $map = [
            'name' => 'r.name',
            'distance' => 'r.distance',
            'elevation_gain' => 'r.elevation_gain',
            'athlete' => 'a.name',
            'date' => 'r.updatedAt',
        ];

        if ($sort = $request->query->get('sort', null)) {
            $sort = $map[$sort];
            $dir = $request->query->get('dir', 'desc');
            $orderBy = [$sort => $dir];
        }

        $routes = $repository->findByFilters($filters, $orderBy ?? null, $perPage, ($page - 1) * $perPage);

        return $this->render('routes/list-routes.html.twig', [
            'data' => $routes,
            'per_page' => $perPage,
            'pages' => $routes['pages'],
            'route_add_form' => $addRouteForm->createView(),
            'route_filter_form' => $routeFilterForm->createView(),
            'filter_values' => $routeService->getFiltersForDisplay(
                array_filter(
                    $request->query->all('filter') ?? [],
                ),
            ),
        ]);
    }
}
