<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Route;
use App\Service\GeoNamesService;
use App\Service\RouteService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleSectionOutput;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'route:tags', description: 'Tag routes.')]
class RouteTagCommand extends Command
{
    private InputInterface $input;
    private OutputInterface $output;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly RouteService $routeService,
        private readonly GeoNamesService $geoNamesService,
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->addOption(
                'route',
                null,
                InputOption::VALUE_OPTIONAL
            )
            ->addOption(
                'athlete',
                null,
                InputOption::VALUE_OPTIONAL
            )
            ->addOption(
                'force',
                null,
                InputOption::VALUE_NONE
            )
            ->addOption(
                'save-empty',
                null,
                InputOption::VALUE_NONE
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        try {
            if ($routeId = $input->getOption('route')) {
                $route = $this->routeService->load($routeId);
                $this->processRoute($route);
            }
            elseif ($athleteId = $input->getOption('athlete')) {
                /** @var \App\Repository\RouteRepository $repository */
                $repository = $this->entityManager->getRepository(Route::class);
                $routeIds = $repository->findAllNotTagged((int) $athleteId);

                if (!empty($routeIds)) {
                    foreach ($routeIds as $routeId) {
                        $route = $repository->find($routeId);
                        $this->processRoute($route);
                    }
                } else {
                    $this->output->writeln('No routes without tags found.');
                }
            }
            // Process all routes without tags.
            elseif (!$routeId = $input->getOption('route')) {
                /** @var \App\Repository\RouteRepository $repository */
                $repository = $this->entityManager->getRepository(Route::class);

                // If --force was set, process *all* routes (even those which already have tags fetched).
                /** @var \App\Entity\Route[] $routes */
                if ($input->getOption('force')) {
                    $routes = $repository->findAll();
                } else {
                    $routes = $repository->findAllNotTagged();
                }

                if (!empty($routes)) {
                    foreach ($routes as $route) {
                        $this->processRoute($route);
                    }
                } else {
                    $this->output->writeln('No routes without tags found.');
                }
            }
        } catch (\Exception $e) {
            $this->output->writeln(strtr('Error: <error>%error%</error>', ['%error%' => $e->getMessage()]));

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    private function processRoute(Route $route)
    {
        $this->output->write(strtr('<info>%name%</info> (%id%) by <comment>%athlete%</comment> (%athlete_id%) ... ', [
            '%name%' => $route->getName(),
            '%id%' => $route->getId(),
            '%athlete%' => $route->getAthlete()->getName(),
            '%athlete_id%' => $route->getAthlete()->getId(),
        ]));

        $list = \Polyline::decode($route->getPolylineSummary());
        $pairs = \Polyline::pair($list);

        $tags = [];
        foreach ($pairs as $pair) {
            $response = $this->geoNamesService->reverseGeocode($pair[0], $pair[1]);

            foreach ($response as $geoname) {
                if ($geoname) {
                    $tags[$geoname['id']] = $geoname['name'];
                }
            }
        }

        $tags = array_filter($tags);
        $tags = array_unique($tags);

        if (!empty($tags) || $this->input->getOption('save-empty')) {
            $route->setTags(array_values($tags));
            $this->entityManager->flush();
        }

        $this->output->writeln(implode(', ', $tags));
    }
}
