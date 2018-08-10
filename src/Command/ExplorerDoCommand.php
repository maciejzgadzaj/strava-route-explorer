<?php

namespace App\Command;

use App\Entity\Athlete;
use App\Entity\Route;
use App\Service\AthleteService;
use App\Service\MapService;
use App\Service\RouteService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ExplorerDoCommand
 *
 * @package App\Command
 */
class ExplorerDoCommand extends ContainerAwareCommand
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
     * ExplorerDoCommand constructor.
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
        AthleteService $athleteService
    ) {
        parent::__construct($name);

        $this->entityManager = $entityManager;
        $this->routeService = $routeService;
        $this->mapService = $mapService;
        $this->athleteService = $athleteService;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('explorer:do')
            ->setDescription('Does what necessary.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var \App\Entity\Route[] $routes */
        $routes = $this->entityManager->getRepository(Route::class)->findAll();
    }
}
