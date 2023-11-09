<?php

declare(strict_types=1);

namespace App\Action\Routes;

use App\Service\RouteService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/routes/add/{route_id}', name: 'routes_add')]
#[IsGranted('ROLE_USER')]
class AddRoute extends AbstractController
{
    public function __invoke(int $route_id, RouteService $routeService): RedirectResponse
    {
        if ($route = $routeService->syncRoute($route_id)) {
            $redirectParams = ['filter[name]' => $route->getId()];
        }

        return $this->redirectToRoute('routes', $redirectParams ?? []);
    }
}
