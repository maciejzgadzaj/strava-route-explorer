<?php

declare(strict_types=1);

namespace App\Action\Athletes;

use App\Entity\Athlete;
use App\Service\StravaService;
use Doctrine\ORM\EntityManagerInterface;
use JetBrains\PhpStorm\NoReturn;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route(path: '/athletes/{athleteId}/dump', name: 'athlete_dump')]
#[IsGranted('ROLE_ADMIN')]
class Dump extends AbstractController
{
    #[NoReturn]
    public function __invoke(
        int $athleteId,
        EntityManagerInterface $entityManager,
        StravaService $stravaService,
    ): void {
        $localAthlete = $entityManager->getRepository(Athlete::class)->find($athleteId);
        dump('$localAthlete', $localAthlete);
        dump('$localAthlete->routes', $localAthlete->getRoutes()->toArray());
        dump('$localAthlete->starredRoutes', $localAthlete->getStarredRoutes()->toArray());

        try {
            $stravaAthlete = $stravaService->getAthlete($athleteId);
            dump('$stravaAthlete', $stravaAthlete);
        } catch (\Exception $exception) {
            dump('$stravaAthlete', $exception?->getMessage());
        }

        exit;
    }
}
