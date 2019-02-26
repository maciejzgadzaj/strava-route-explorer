<?php

namespace App\Command;

use App\Entity\Athlete;
use App\Service\StravaService;
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
class ExplorerAthleteMigrateCommand extends ContainerAwareCommand
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var \App\Service\StravaService
     */
    private $stravaService;

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
     * @param \App\Service\StravaService $stravaService
     */
    public function __construct(
        string $name = null,
        EntityManagerInterface $entityManager,
        StravaService $stravaService
    ) {
        parent::__construct($name);

        $this->entityManager = $entityManager;
        $this->stravaService = $stravaService;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('explorer:athlete:migrate')
            ->setDescription('Migrate athlete.')
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
            $this->migrateAthlete($athlete);
        }
        else {
            foreach ($repository->findAll() as $athlete) {
                $this->migrateAthlete($athlete);
            }
        }
    }

    /**
     * Migrate athlete's forever token to new refresh_token.
     *
     * @param \App\Entity\Athlete $athlete
     */
    function migrateAthlete(Athlete $athlete)
    {
        try {
            $this->output->write(strtr('Athlete %name% (%id%): ', [
                '%name%' => $athlete->getUsername(),
                '%id%' => $athlete->getId(),
            ]));

            if (!$athlete->getAccessToken()) {
                $this->output->writeln('not authorized with Strava.');
                return;
            }

            if ($athlete->getRefreshToken()) {
                $this->output->writeln('<comment>already migrated</comment>.');
                return;
            }

            $this->stravaService->getAthleteAccessToken($athlete);
            $this->output->writeln('<info>migrated successfully</info>.');

        } catch (\Exception $e) {
            $this->output->writeln('Error: '.$e->getMessage());
        }
    }
}
