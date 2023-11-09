<?php

declare(strict_types=1);

namespace App\Action\Strava;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * @see https://developers.strava.com/docs/authentication/
 */
#[Route(path: '/strava/deauth', name: 'strava_deauth')]
#[IsGranted('ROLE_USER')]
class Deauth extends AbstractController
{
    public function __invoke(Request $request, Security $security): RedirectResponse
    {
        $security->logout(false);

        return $this->redirectToRoute('homepage');
    }
}
