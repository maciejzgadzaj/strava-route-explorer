<?php

declare(strict_types=1);

namespace App\Action\Strava;

use App\Service\AthleteService;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @see https://developers.strava.com/docs/authentication/
 */
#[Route(path: '/strava/auth/redirect', name: 'strava_auth_redirect')]
class AuthRedirect extends AbstractController
{
    public function __invoke(
        Request $request,
        HttpClientInterface $stravaClient,
        AthleteService $athleteService,
        Security $security,
        LoggerInterface $logger,
    ): RedirectResponse {
        $query = $request->query->all();

        if (empty($query['code'])) {
            $this->addFlash('error', 'Strava authorization failed.');

            return $this->redirectToRoute('homepage');
        }

        if (!str_contains($query['scope'], 'read_all')) {
            $this->addFlash('error', strtr('Please select "View your private non-activity data such as segments and routes" to sync your private routes too. <a href="@strava_auth_url">Re-authorize</a>?', [
                '@strava_auth_url' => $this->generateUrl('strava_auth'),
            ]));
        }

        $params = [
            'client_id' => $this->getParameter('strava_client_id'),
            'client_secret' => $this->getParameter('strava_client_secret'),
            'code' => $query['code'],
            'grant_type' => 'authorization_code',
        ];
        try {
            $response = $stravaClient->request('POST', '/oauth/token', ['body' => $params]);
        } catch (\Exception $exception) {
            $logger->error('Error fetching OAuth token from Strava', $params + [
                'exception' => $exception->getMessage(),
            ]);
            $this->addFlash('error', 'Strava authorization failed.');

            return $this->redirectToRoute('homepage');
        }

        $content = $response->toArray();
        $athlete = $athleteService->save($content['athlete'], $content);

        $security->login($athlete);

        return $this->redirectToRoute('welcome');
    }
}
