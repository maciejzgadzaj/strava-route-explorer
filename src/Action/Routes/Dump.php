<?php

declare(strict_types=1);

namespace App\Action\Routes;

use App\Action\AdminAction;
use App\Entity\Route as RouteEntity;
use App\Service\StravaService;
use Doctrine\ORM\EntityManagerInterface;
use JetBrains\PhpStorm\NoReturn;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/routes/{routeId}/dump', name: 'route_dump')]
#[IsGranted('ROLE_ADMIN')]
class Dump extends AbstractController
{
    #[NoReturn]
    public function __invoke(
        int $routeId,
        EntityManagerInterface $entityManager,
        StravaService $stravaService,
    ): void {
        $localRoute = $entityManager->getRepository(RouteEntity::class)->find($routeId);
        dump('$localRoute', $localRoute);
        dump('$localRoute->starredBy', $localRoute->getStarredBy()->toArray());

        try {
            $stravaRoute = $stravaService->getRoute($routeId);
            dump('$stravaRoute', $stravaRoute);
        } catch (\Exception $exception) {
            dump('$stravaRoute', $exception?->getMessage());
        }

        exit;
    }
}
