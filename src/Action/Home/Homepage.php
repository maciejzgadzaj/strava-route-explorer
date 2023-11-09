<?php

declare(strict_types=1);

namespace App\Action\Home;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/', name: 'homepage')]
class Homepage extends AbstractController
{
    public function __invoke(): Response
    {
        return $this->render('home/homepage.html.twig');
    }
}
