<?php

declare(strict_types=1);

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

// https://symfony.com/doc/current/security/access_denied_handler.html#customize-the-unauthorized-response
class AuthenticationEntryPoint implements AuthenticationEntryPointInterface
{
    public function __construct(private readonly UrlGeneratorInterface $urlGenerator)
    {
    }

    public function start(Request $request, AuthenticationException $authException = null): RedirectResponse
    {
        $request->getSession()->getFlashBag()
            ->add('error', 'Please connect with Strava first to be able to access that page.');

        return new RedirectResponse($this->urlGenerator->generate('homepage'));
    }
}
