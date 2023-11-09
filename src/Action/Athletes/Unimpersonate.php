<?php

declare(strict_types=1);

namespace App\Action\Athletes;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

// https://symfony.com/doc/current/security/impersonating_user.html
#[Route(path: '/athletes/unimpersonate', name: 'athletes_unimpersonate')]
#[IsGranted('ROLE_USER')]
class Unimpersonate extends AbstractController
{
    public function __invoke(): RedirectResponse
    {
        return $this->redirectToRoute('athletes', [
            '_switch_user' => '_exit',
        ]);
    }
}
