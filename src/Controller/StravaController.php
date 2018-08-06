<?php

namespace App\Controller;

use App\Service\AthleteService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class StravaController
 *
 * @package App\Controller
 */
class StravaController extends Controller
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
            'scope' => 'public',
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

        /** @var \GuzzleHttp\Client $client */
        $client = $this->get('csa_guzzle.client.strava');
        $params = [
            'client_id' => $this->getParameter('strava_client_id'),
            'client_secret' => $this->getParameter('strava_client_secret'),
            'code' => $query['code'],
        ];
        $response = $client->post('/oauth/token', ['form_params' => $params]);
        $content = \GuzzleHttp\json_decode($response->getBody()->getContents());

        $athleteService->save($content->athlete, $content->access_token);

        $session->set('athlete', $content->athlete);
        $session->set('strava_access_token', $content->access_token);

        return $this->redirectToRoute('routes_sync');
    }
}
