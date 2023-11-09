<?php

declare(strict_types=1);

namespace App\Action\Routes;

use App\Service\PhotonService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * This route is called from templates/routes/list-routes.html.twig, but using the "path" value
 * instead of route "name", to avoid installing the FOSJsRoutingBundle just for this single use case
 * (see https://symfony.com/doc/4.1/routing/generate_url_javascript.html).
 */
#[Route(path: '/routes/autocomplete/location', name: 'routes_autocomplete_location')]
#[IsGranted('ROLE_USER')]
class AutocompleteLocation extends AbstractController
{
    public function __invoke(Request $request, PhotonService $photonService): JsonResponse
    {
        $name = trim(strip_tags($request->get('term')));

        $names = $photonService->getLocationsByName($name);

        return new JsonResponse($names);
    }
}
