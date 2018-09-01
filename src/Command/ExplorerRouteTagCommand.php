<?php

namespace App\Command;

use App\Entity\Route;
use App\Service\GeoNamesService;
use App\Service\RouteService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBar;

/**
 * Class ExplorerRouteTagCommand
 *
 * @package App\Command
 */
class ExplorerRouteTagCommand extends ContainerAwareCommand
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
     * @var \App\Service\GeoNamesService
     */
    private $geoNamesService;

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
     * @var \Symfony\Component\Console\Output\ConsoleSectionOutput
     */
    private $section1;

    /**
     * @var \Symfony\Component\Console\Output\ConsoleSectionOutput
     */
    private $section2;

    /**
     * @var \Symfony\Component\Console\Helper\ProgressBar
     */
    private $progressBar1;

    /**
     * @var \Symfony\Component\Console\Helper\ProgressBar
     */
    private $progressBar2;

    /**
     * @var array
     */
    private $data = [];

    /**
     * @var int
     */
    private $tagsCreated;

    /**
     * @var int
     */
    private $tagsAddedToRoute;

    /**
     * ExplorerRouteTagCommand constructor.
     *
     * @param string|null $name
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param \Psr\Log\LoggerInterface $logger
     * @param \App\Service\RouteService $routeService
     * @param \App\Service\GeoNamesService $geoNamesService
     */
    public function __construct(
        string $name = null,
        EntityManagerInterface $entityManager,
        LoggerInterface $logger,
        RouteService $routeService,
        GeoNamesService $geoNamesService
    ) {
        parent::__construct($name);

        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->routeService = $routeService;
        $this->geoNamesService = $geoNamesService;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('explorer:route:tag')
            ->setDescription('Tag routes.')
            ->addOption(
                'route',
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

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        ProgressBar::setFormatDefinition('custom1', " %current%/%max% [%bar%] %percent:3s%% %message%");
        ProgressBar::setFormatDefinition('custom2', ' %current%/%max% [%bar%] %percent:3s%%');

        $this->section1 = $this->output->section();
        $this->section2 = $this->output->section();

        $this->progressBar1 = new ProgressBar($this->section1, 0);
        $this->progressBar1->setFormat('custom1');
        $this->progressBar2 = new ProgressBar($this->section2, 0);
        $this->progressBar2->setFormat('custom2');

        try {
            // Process all routes without tags.
            if (!$routeId = $input->getOption('route')) {
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
                    $this->progressBar1->setMaxSteps(count($routes));
                    $this->progressBar1->start();

                    foreach ($routes as $route) {
                        $this->processRoute($route);
                    }

                    $this->progressBar1->finish();
                } else {
                    $this->output->writeln('No routes without tags found.');
                }
            } else {
                // Process single route.
                $this->progressBar1->setMaxSteps(1);
                $this->progressBar1->start();

                $route = $this->routeService->load($routeId);
                $this->processRoute($route);

                $this->progressBar1->finish();
            }
        } catch (\Exception $e) {
            $this->output->writeln(strtr('Error: <error>%error%</error>', ['%error%' => $e->getMessage()]));
        }
    }

    /**
     * Fetch tags and update the route.
     *
     * @param \App\Entity\Route $route
     */
    private function processRoute(Route $route)
    {
        $this->tagsCreated = $this->tagsAddedToRoute = 0;
        $this->data = [];

        $this->progressBar1->setMessage(strtr('<info>%name%</info> (%id%) by <comment>%athlete%</comment>', [
            '%name%' => $route->getName(),
            '%id%' => $route->getId(),
            '%athlete%' => $route->getAthlete()->getName(),
        ]));
        $this->progressBar1->display();

        $list = \Polyline::decode($route->getPolylineSummary());
        $pairs = \Polyline::pair($list);

        $this->progressBar2->setProgress(0);
        $this->progressBar2->setMaxSteps(count($pairs));
        $this->progressBar2->start();

        $this->data = [];
        foreach ($pairs as $delta => $pair) {
            $response = $this->geoNamesService->reverseGeocode($pair[0], $pair[1]);

            foreach ($response as $geoname) {
                $value = $geoname['name'];
                $this->data[$value] = $value;
            }

            $this->progressBar2->advance();
        }

        if (!empty($this->data) || $this->input->getOption('save-empty')) {
            $route->setTags(array_values($this->data));
            $this->entityManager->flush();
        }

        $this->progressBar1->advance();
    }
}
