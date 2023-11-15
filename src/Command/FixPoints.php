<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Route;
use Doctrine\ORM\EntityManagerInterface;
use LongitudeOne\Spatial\PHP\Types\Geometry\Point;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'fix-points')]
class FixPoints extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function configure()
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $routes = $this->entityManager->createQueryBuilder()
            ->select('r.id')
            ->from('App\Entity\Route', 'r')
            ->getQuery()
            ->toIterable()
        ;

        $minx = $maxx = $miny = $maxy = $updated = 0;

        foreach ($routes as $i => $routeData) {
            $route = $this->entityManager->find(Route::class, $routeData['id']);

            $polyline = $route->getPolylineSummary();
            $list = \Polyline::decode($polyline);
            $pairs = \Polyline::pair($list);

            $start = reset($pairs);
            $startPoint = new Point($start[1], $start[0]);
            $route->setStart($startPoint);

            $minx = min($minx, $start[1]);
            $maxx = max($maxx, $start[1]);
            $miny = min($miny, $start[0]);
            $maxy = max($maxy, $start[0]);

            $end = end($pairs);
            $endPoint = new Point($end[1], $end[0]);
            $route->setEnd($endPoint);

            $minx = min($minx, $end[1]);
            $maxx = max($maxx, $end[1]);
            $miny = min($miny, $end[0]);
            $maxy = max($maxy, $end[0]);

            $this->entityManager->persist($route);

            if (0 === $i % 100) {
                dump($i);
                $this->entityManager->flush();
                $this->entityManager->clear();
            }

            $updated++;
        }

        printf("Updated $updated routes\n\n");
        printf("longitude:\n");
        printf("minX: $minx\n");
        printf("maxX: $maxx\n\n");
        printf("latitude:\n");
        printf("minY: $miny\n");
        printf("maxY: $maxy\n");

        return Command::SUCCESS;
    }
}
