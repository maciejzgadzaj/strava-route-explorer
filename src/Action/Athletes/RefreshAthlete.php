<?php

declare(strict_types=1);

namespace App\Action\Athletes;

use App\Service\AthleteService;
use App\Service\StravaService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * @see https://developers.strava.com/docs/reference/#api-Athletes-getLoggedInAthlete
 */
#[Route(path: '/athlete/{athleteId}/refresh', name: 'strava_athlete_refresh')]
#[IsGranted('ROLE_SUPER_ADMIN')]
class RefreshAthlete extends AbstractController
{
    public function __invoke(
        int $athleteId,
        Request $request,
        StravaService $stravaService,
        AthleteService $athleteService
    ): RedirectResponse {
        $previousPage = $request->headers->get('referer');

        try {
            $athleteData = $stravaService->getAthlete($athleteId);

            $athleteService->save($athleteData);
        } catch (ClientException $exception) {
            $this->addFlash('error', 'Error fetching athlete from Strava: '.$exception->getMessage());

            return $this->redirect($previousPage);
        }

        $this->addFlash('success', 'Athlete refreshed successfully.');

        return $this->redirect($previousPage);
    }
}
