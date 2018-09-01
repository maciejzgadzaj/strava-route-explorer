<?php

namespace App\Command;

use App\Entity\Route;
use App\Service\GeoNamesService;
use App\Service\RouteService;
use App\Service\StravaService;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Exception\ClientException;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBar;

/**
 * Class ExplorerRouteSegmentsCommand
 *
 * @package App\Command
 */
class ExplorerRouteSegmentsCommand extends ContainerAwareCommand
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \App\Service\RouteService
     */
    private $routeService;

    /**
     * @var \App\Service\StravaService
     */
    private $stravaService;

    /**
     * @var int
     */
    private $routeId;

    /**
     * @var \Symfony\Component\Console\Input\InputInterface
     */
    private $input;

    /**
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    private $output;

    /**
     * @var array
     */
    private $data = [];

    /**
     * ExplorerRouteSegmentsCommand constructor.
     *
     * @param string|null $name
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param \Psr\Log\LoggerInterface $logger
     * @param \App\Service\RouteService $routeService
     * @param \App\Service\StravaService $stravaService
     */
    public function __construct(
        string $name = null,
        EntityManagerInterface $entityManager,
        LoggerInterface $logger,
        RouteService $routeService,
        StravaService $stravaService
    ) {
        parent::__construct($name);

        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->routeService = $routeService;
        $this->stravaService = $stravaService;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('explorer:route:segments')
            ->setDescription('Fetch segments for routes.')
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

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

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
                }

                if (!empty($routes)) {
                    $progressBar = new ProgressBar($this->output, count($routes));
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
                        } catch (ClientException $e) {
                            $content = \GuzzleHttp\json_decode($e->getResponse()->getBody()->getContents());
                            // If route was not found on Strava, let's delete it locally too.
                            if ($e->getCode() == 404 && $content->message == 'Record Not Found') {
                                $this->routeService->delete($route->getId());
                                $this->output->writeln(
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
                                throw $e;
                            }
                        }

                        $progressBar->advance();
                    }

                    $progressBar->finish();
                } else {
                    $this->output->writeln('No routes without segments found.');
                }
            } else {
                // Process single route.
                $route = $this->routeService->load($routeId);
                $this->processRoute($route);

                $this->output->writeln(strtr('Processed route <info>%name%</info> (%id%) by <comment>%athlete%</comment>', [
                    '%name%' => $route->getName(),
                    '%id%' => $route->getId(),
                    '%athlete%' => $route->getAthlete()->getName(),
                ]));
            }
        } catch (\Exception $e) {
            $this->output->writeln(strtr('Error: <error>%error%</error>', ['%error%' => $e->getMessage()]));
        }
    }

    /**
     * Fetch route details from Strava and save segment names locally.
     *
     * @param \App\Entity\Route $route
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function processRoute($route)
    {
        $content = $this->stravaService->getRoute($route->getId());

        $this->data = [];

        if (!empty($content->segments)) {
            foreach ($content->segments as $segment) {
                $this->data[$segment->id] = $segment->name;
            }
        }

        $route->setSegments($this->data);

        $this->entityManager->flush();
    }
}
