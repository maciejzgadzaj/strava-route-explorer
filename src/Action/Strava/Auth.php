<?php

declare(strict_types=1);

namespace App\Action\Strava;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @see https://developers.strava.com/docs/authentication/
 */
#[Route(path: '/strava/auth', name: 'strava_auth')]
class Auth extends AbstractController
{
    public function __invoke(): RedirectResponse
    {
        $params = [
            'client_id' => $this->getParameter('strava_client_id'),
            'redirect_uri' => $this->generateUrl('strava_auth_redirect', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'response_type' => 'code',
            'approval_prompt' => 'auto',
            'scope' => 'read_all',
            'state' => 'auth',
        ];

        $url = 'https://www.strava.com/oauth/authorize?'.http_build_query($params);

        return $this->redirect($url);
    }
}
