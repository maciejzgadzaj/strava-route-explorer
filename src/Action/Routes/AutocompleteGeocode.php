<?php

declare(strict_types=1);

namespace App\Action\Routes;

use App\Service\PhotonService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/routes/autocomplete/reverse', name: 'routes_autocomplete_reverse')]
#[IsGranted('ROLE_USER')]
class AutocompleteGeocode extends AbstractController
{
    public function __invoke(Request $request, PhotonService $photonService): JsonResponse
    {
        $location = $photonService->reverseGeocode(
            (float) $request->get('lat'),
            (float) $request->get('lon'),
        );

        return new JsonResponse($location);
    }
}
