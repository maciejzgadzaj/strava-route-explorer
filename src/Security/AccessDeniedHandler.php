<?php

declare(strict_types=1);

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Authorization\AccessDeniedHandlerInterface;

// https://symfony.com/doc/current/security/access_denied_handler.html#customize-the-unauthorized-response
class AccessDeniedHandler implements AccessDeniedHandlerInterface
{
    public function __construct(private readonly UrlGeneratorInterface $urlGenerator)
    {
    }

    public function handle(Request $request, AccessDeniedException $accessDeniedException): RedirectResponse
    {
        $request->getSession()->getFlashBag()
            ->add('error', 'You have insufficient permissions to access that page.');

        if ($previousPage = $request->headers->get('referer')) {
            return new RedirectResponse($previousPage);
        }

        return new RedirectResponse($this->urlGenerator->generate('homepage'));
    }
}
