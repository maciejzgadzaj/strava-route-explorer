<?php

namespace App\Command;

use App\Entity\Athlete;
use App\Entity\Route;
use App\Service\RouteService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ExplorerAthleteStatsCommand
 *
 * @package App\Command
 */
class ExplorerAthletePhotoCommand extends ContainerAwareCommand
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
     * @var \Symfony\Component\Console\Input\InputInterface
     */
    private $input;

    /**
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    private $output;

    /**
     * ExplorerAthleteStatsCommand constructor.
     *
     * @param string|null $name
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param \App\Service\RouteService $routeService
     */
    public function __construct(
        string $name = null,
        EntityManagerInterface $entityManager,
        RouteService $routeService
    ) {
        parent::__construct($name);

        $this->entityManager = $entityManager;
        $this->routeService = $routeService;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('explorer:athlete:photo')
            ->setDescription('Refresh profile photos.')
            ->addOption(
                'athlete',
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

        $this->input = $input;
        $this->output = $output;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var \App\Repository\AthleteRepository $repository */
        $repository = $this->entityManager->getRepository(Athlete::class);

        if ($athleteId = $input->getOption('athlete')) {
            $athlete = $repository->find($athleteId);
            $this->refreshProfilePhoto($athlete);
        }
        else {
            foreach ($repository->findAll() as $athlete) {
                $this->refreshProfilePhoto($athlete);
            }
        }
    }

    /**
     * Refresh athlete's profile photo.
     *
     * @param \App\Entity\Athlete $athlete
     */
    function refreshProfilePhoto(Athlete $athlete)
    {
        try {
            $this->output->writeln(strtr('%name% (%id%)', [
                '%name%' => $athlete->getUsername(),
                '%id%' => $athlete->getId(),
            ]));

            /** @var \App\Repository\RouteRepository $repository */
            $repository = $this->entityManager->getRepository(Route::class);

            /** @var \App\Entity\Route $route */
            if ($route = $repository->findOneBy(['athlete' => $athlete->getId()])) {
                $this->routeService->syncRoute($route->getId());
            }
        }
        catch (\Exception $e) {
            $this->output->writeln('Error: '.$e->getMessage());
        }
    }

}
