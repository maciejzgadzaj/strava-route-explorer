<?php

declare(strict_types=1);

namespace App\Action\Home;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/welcome', name: 'welcome')]
#[IsGranted('ROLE_USER')]
class Welcome extends AbstractController
{
    public function __invoke(): Response
    {
        return $this->render('home/welcome.html.twig');
    }
}
