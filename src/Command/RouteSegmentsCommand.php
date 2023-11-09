<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Route;
use App\Service\RouteService;
use App\Service\StravaService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use function array_column;
use function array_combine;
use function get_class;

#[AsCommand(name: 'route:segments', description: 'Fetch segments for routes.')]
class RouteSegmentsCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly RouteService $routeService,
        private readonly StravaService $stravaService
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
                'force',
                null,
                InputOption::VALUE_NONE
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            // Process all routes without segments.
            if (!$routeId = $input->getOption('route')) {
                /** @var \App\Repository\RouteRepository $repository */
                $repository = $this->entityManager->getRepository(Route::class);

                // If --force was set, process *all* routes (even those which already have segments fetched).
                /** @var \App\Entity\Route[] $routes */
                if ($input->getOption('force')) {
                    $routes = $repository->findAll();
                } else {
                    $routes = $repository->findAllWithoutSegments();
                    dump($routes);
                    die;
                }

                if (!empty($routes)) {
                    $progressBar = new ProgressBar($output, count($routes));
                    $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');
                    $progressBar->start();

                    foreach ($routes as $route) {
                        try {
                            $progressBar->setMessage(strtr('<info>%name%</info> (%id%) by <comment>%athlete%</comment>', [
                                '%name%' => $route->getName(),
                                '%id%' => $route->getId(),
                                '%athlete%' => $route->getAthlete()->getName(),
                            ]));
                            $progressBar->display();

                            $this->processRoute($route);
                        } catch (ClientException $exception) {
                            $content = $exception->getResponse()->toArray();
                            // If route was not found on Strava, let's delete it locally too.
                            if ($exception->getCode() == 404 && $content['message'] === 'Record Not Found') {
//                                $this->routeService->delete($route->getId());
                                $output->writeln(
                                    strtr(
                                        'Deleted route %route_name% (%route_id%) by %athlete_name%',
                                        [
                                            '%route_name%' => $route->getName(),
                                            '%route_id%' => $route->getId(),
                                            '%athlete_name%' => $route->getAthlete()->getName(),
                                        ]
                                    )
                                );
                            } else {
                                // Re-throw any other exception (usually exceeded Strava quota limit).
                                throw $exception;
                            }
                        }

                        $progressBar->advance();
                    }

                    $progressBar->finish();
                } else {
                    $output->writeln('No routes without segments found.');
                }
            } else {
                // Process single route.
                $route = $this->routeService->load($routeId);
                $this->processRoute($route);

                $output->writeln(strtr('Processed route <info>%name%</info> (%id%) by <comment>%athlete%</comment>', [
                    '%name%' => $route->getName(),
                    '%id%' => $route->getId(),
                    '%athlete%' => $route->getAthlete()->getName(),
                ]));
            }
        } catch (\Exception $e) {
            $output->writeln(strtr('Error: <error>%error%</error>', ['%error%' => $e->getMessage()]));

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    private function processRoute(Route $route)
    {
        $routeData = $this->stravaService->getRoute($route->getId());

        $segments = array_combine(
            array_column($routeData['segments'], 'id'),
            array_column($routeData['segments'], 'name'),
        );

        $route->setSegments($segments);

        $this->entityManager->flush();
    }
}
