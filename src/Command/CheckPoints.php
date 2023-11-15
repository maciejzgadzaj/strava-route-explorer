<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'check-points')]
class CheckPoints extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $routes = $this->entityManager->createQueryBuilder()
            ->select('r.id')
            ->from('App\Entity\Route', 'r')
            ->getQuery()
            ->toIterable()
        ;

        $minx = $maxx = $miny = $maxy = $count = 0;

        foreach ($routes as $i => $routeData) {
            $route = $this->entityManager->find(Route::class, $routeData['id']);

            $start = $route->getStart();
            $minx = min($minx, $start->getLongitude());
            $maxx = max($maxx, $start->getLongitude());
            $miny = min($miny, $start->getLatitude());
            $maxy = max($maxy, $start->getLatitude());

            $end = $route->getEnd();
            $minx = min($minx, $end->getLongitude());
            $maxx = max($maxx, $end->getLongitude());
            $miny = min($miny, $end->getLatitude());
            $maxy = max($maxy, $end->getLatitude());

            if (0 === $i % 100) {
                dump($i);
                $this->entityManager->clear();
            }

            $count++;
        }

        printf("$count routes\n\n");
        printf("longitude:\n");
        printf("minX: $minx\n");
        printf("maxX: $maxx\n\n");
        printf("latitude:\n");
        printf("minY: $miny\n");
        printf("maxY: $maxy\n");

        return Command::SUCCESS;
    }
}
