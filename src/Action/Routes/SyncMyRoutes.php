<?php

declare(strict_types=1);

namespace App\Action\Routes;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/sync-my-routes', name: 'sync_my_routes')]
#[IsGranted('ROLE_USER')]
class SyncMyRoutes extends AbstractController
{
    public function __invoke(LoggerInterface $logger): Response
    {
        $logger->info('Synchronizing user routes from Strava');

        return $this->render('routes/sync-my-routes.html.twig');
    }
}
