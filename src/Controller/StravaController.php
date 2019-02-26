<?php

namespace App\Controller;

use App\Service\AthleteService;
use App\Service\StravaService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class StravaController
 *
 * @package App\Controller
 */
class StravaController extends ControllerBase
{
    /**
     * Initiate Strava OAuth flow.
     *
     * @Route("/strava/auth", name="strava_auth")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @see https://developers.strava.com/docs/authentication/
     */
    public function authAction()
    {
        $params = [
            'client_id' => $this->getParameter('strava_client_id'),
            'redirect_uri' => $this->generateUrl('strava_auth_redirect', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'response_type' => 'code',
            'approval_prompt' => 'auto',
            'scope' => 'read_all',
            'state' => 'auth',
        ];
        return $this->redirect('https://www.strava.com/oauth/authorize?'.http_build_query($params));
    }

    /**
     * Strava OAuth redirect callback and token exchange.
     *
     * @Route("/strava/auth/redirect", name="strava_auth_redirect")
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Symfony\Component\HttpFoundation\Session\Session $session
     * @param \App\Service\AthleteService $athleteService
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @see https://developers.strava.com/docs/authentication/
     */
    public function redirectAction(Request $request, Session $session, AthleteService $athleteService)
    {
        $query = $request->query->all();

        // Redirect back to home page on failed Strava authorization.
        if (empty($query['code'])) {
            $session->getFlashBag()->add('error', 'Strava authorization failed.');
            return $this->redirectToRoute('homepage');
        }

        if (strpos($query['scope'], 'read_all') === FALSE) {
            $session->getFlashBag()->add('error', strtr('Please select "View your private non-activity data such as segments and routes" to sync your private routes too. <a href="@strava_auth_url">Re-authorize</a>?', [
                '@strava_auth_url' => $this->generateUrl('strava_auth'),
            ]));
        }

        /** @var \GuzzleHttp\Client $client */
        $client = $this->get('csa_guzzle.client.strava');
        $params = [
            'client_id' => $this->getParameter('strava_client_id'),
            'client_secret' => $this->getParameter('strava_client_secret'),
            'code' => $query['code'],
            'grant_type' => 'authorization_code',
        ];
        $response = $client->post('/oauth/token', ['form_params' => $params]);
        $content = \GuzzleHttp\json_decode($response->getBody()->getContents());

        $athlete = $athleteService->save($content->athlete, $content);

        $session->set('strava_athlete', $athlete->getId());

        $message = 'Authorized with Strava: %athlete_name% (%athlete_id%).';
        $params = [
            '%athlete_name%' => $athlete->getName(),
            '%athlete_id%' => $athlete->getid(),
        ];
        $this->logger->info(strtr($message, $params));

        return $this->redirectToRoute('routes_sync', ['athlete_id' => $content->athlete->id]);
    }

    /**
     * Remove application cookies.
     *
     * @Route("/strava/deauth", name="strava_deauth")
     *
     * @param \Symfony\Component\HttpFoundation\Session\SessionInterface $session
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @see https://developers.strava.com/docs/authentication/
     */
    public function deauthAction(SessionInterface $session)
    {
        // Current cookie.
        $session->remove('strava_athlete');
        // Old cookies.
        $session->remove('athlete');
        $session->remove('strava_access_token');

        $this->logger->debug('Removed athlete cookies.');

        return $this->redirectToRoute('homepage');
    }
}
