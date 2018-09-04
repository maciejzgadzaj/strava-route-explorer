<?php

namespace App\Command;

use App\Entity\Athlete;
use App\Entity\Route;
use App\Service\AthleteService;
use App\Service\GeoNamesService;
use App\Service\MapService;
use App\Service\RouteService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ExplorerTestGeonamesCommand
 *
 * @package App\Command
 */
class ExplorerTestGeonamesCommand extends ContainerAwareCommand
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var \App\Service\RouteService
     */
    private $routeService;

    /**
     * @var \App\Service\MapService
     */
    private $mapService;

    /**
     * @var \App\Service\AthleteService
     */
    private $athleteService;

    /**
     * @var \App\Service\GeoNamesService
     */
    private $geoNamesService;

    /**
     * @var \App\Entity\Route
     */
    private $route;

    /**
     * ExplorerTestGeonamesCommand constructor.
     *
     * @param string|null $name
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param \App\Service\RouteService $routeService
     * @param \App\Service\MapService $mapService
     * @param \App\Service\AthleteService $athleteService
     */
    public function __construct(
        string $name = null,
        EntityManagerInterface $entityManager,
        RouteService $routeService,
        MapService $mapService,
        AthleteService $athleteService,
        GeoNamesService $geoNamesService
    ) {
        parent::__construct($name);

        $this->entityManager = $entityManager;
        $this->routeService = $routeService;
        $this->mapService = $mapService;
        $this->athleteService = $athleteService;
        $this->geoNamesService = $geoNamesService;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('explorer:test:geonames')
            ->setDescription('Tests GeoNames.')
            ->addOption(
                'route',
                null,
                InputOption::VALUE_OPTIONAL
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);

        $this->routeId = $input->getOption('route');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
//        $this->route = $this->routeService->load($this->routeId);
//
//        $list = \Polyline::decode($this->route->getPolylineSummary());
//        $pairs = \Polyline::pair($list);

        $response = $this->geoNamesService->reverseGeocode(48.703453, 2.105877);
        dump($response);
    }
}
