<?php

declare(strict_types=1);

namespace App\Action\Athletes;

use App\Service\AthleteService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

// https://symfony.com/doc/current/security/impersonating_user.html
#[Route(path: '/athletes/{athleteId}/impersonate', name: 'athletes_impersonate')]
#[IsGranted('ROLE_SUPER_ADMIN')]
class Impersonate extends AbstractController
{
    public function __invoke(int $athleteId, AthleteService $athleteService): RedirectResponse
    {
        $athleteToImpersonate = $athleteService->load($athleteId);

        return $this->redirectToRoute('routes', [
            '_switch_user' => $athleteToImpersonate->getId(),
            'filter[athlete]' => $athleteToImpersonate->getName(),
        ]);
    }
}
