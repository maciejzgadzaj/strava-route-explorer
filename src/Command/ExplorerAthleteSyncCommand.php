<?php

namespace App\Command;

use App\Entity\Athlete;
use App\Service\AthleteService;
use App\Service\MapService;
use App\Service\RouteService;
use App\Service\StravaService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ExplorerAthleteSyncCommand
 *
 * @package App\Command
 */
class ExplorerAthleteSyncCommand extends ContainerAwareCommand
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
     * @var \App\Service\MapService
     */
    private $mapService;

    /**
     * @var \App\Service\AthleteService
     */
    private $athleteService;

    /**
     * @var \App\Service\StravaService
     */
    private $stravaService;

    /**
     * @var int
     */
    private $athleteId;

    /**
     * @var \Symfony\Component\Console\Input\InputInterface
     */
    private $input;

    /**
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    private $output;

    /**
     * ExplorerAthleteSyncCommand constructor.
     *
     * @param string|null $name
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param \Psr\Log\LoggerInterface $logger
     * @param \App\Service\RouteService $routeService
     * @param \App\Service\MapService $mapService
     * @param \App\Service\AthleteService $athleteService
     * @param \App\Service\StravaService $stravaService
     */
    public function __construct(
        string $name = null,
        EntityManagerInterface $entityManager,
        LoggerInterface $logger,
        RouteService $routeService,
        MapService $mapService,
        AthleteService $athleteService,
        StravaService $stravaService
    ) {
        parent::__construct($name);

        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->routeService = $routeService;
        $this->mapService = $mapService;
        $this->athleteService = $athleteService;
        $this->stravaService = $stravaService;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('explorer:athlete:sync')
            ->setDescription('Sync routes for an athlete.')
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

        $this->athleteId = $input->getOption('athlete');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        $this->logger->debug('Strava athlete sync: start');

        if ($this->athleteId) {
            if (!$athlete = $this->athleteService->load($this->athleteId)) {
                $output->writeln('<error>Athlete not found.</error>');
                return;
            }
            $this->syncRoutes($athlete);
        } else {
            /** @var \App\Entity\Athlete[] $athletes */
            $athletes = $this->athleteService->getAuthorizedWithStrava();
            foreach ($athletes as $athlete) {
                $this->syncRoutes($athlete);
            }
        }

        $this->logger->debug('Strava athlete sync: end');
    }

    /**
     * Sync routes for an athlete.
     *
     * @param \App\Entity\Athlete $athlete
     */
    private function syncRoutes(Athlete $athlete)
    {
        $message = 'Syncing routes for <comment>%athlete_name%</comment> (%athlete_id%).';
        $params = [
            '%athlete_name%' => $athlete->getName(),
            '%athlete_id%' => $athlete->getId(),
        ];
        $this->logger->debug(strtr(strip_tags($message), $params));
        $this->output->writeln(strtr($message, $params));

        if (empty($athlete->getAccessToken())) {
            $message = '- <error>No Strava access token found for athlete.</error>';
            $this->logger->error(strip_tags($message));
            $this->output->writeln($message);
            return;
        }

        $this->output->writeln('- Fetching routes from Strava...');
        $stravaRoutes = $this->stravaService->getAthleteRoutes($athlete);

        $message = '- Routes fetched: <comment>%count%</comment>';
        $params = [
            '%count%' => count($stravaRoutes),
        ];
        $this->logger->debug(strtr(strip_tags($message), $params));
        $this->output->writeln(strtr($message, $params));

        if (empty($stravaRoutes)) {
            $message = '- <error>No routes to sync.</error>';
            $this->logger->debug(strip_tags($message));
            $this->output->writeln($message);
            return;
        }

        $localRoutes = $this->routeService->getAthleteRoutes($athlete);

        $message = '- Local routes found: <comment>%count%</comment>';
        $params = [
            '%count%' => count($localRoutes),
        ];
        $this->logger->debug(strtr(strip_tags($message), $params));
        $this->output->writeln(strtr($message, $params));

        $publicAdded = $publicUpdated = $publicStarred = $publicDeleted = $published = $privateSkipped = $privateDeleted = 0;
        $syncedIds = [];

        foreach ($stravaRoutes as $routeData) {
            if (!$routeData->private) {
                // If a local route already exists, re-use its current public value.
                $localRoute = $this->routeService->load($routeData->id);
                $routeData->public = $localRoute ? $localRoute->isPublic() : true;
                $syncedIds[] = $routeData->id;

                if ($routeData->public) {
                    $published++;
                }

                // Create or update route.
                $route = $this->routeService->save($routeData);

                // If route belongs to a different athlete, and public is set to false
                // by current athlete, let's just not add starred_by value.
                if ($route->getAthlete()->getId() != $athlete->getId() && $routeData->public) {
                    if (!$route->isStarredBy($athlete)) {
                        $message = '- Starred new route: <info>%route_name%</info> (%route_id%)';
                        $params = [
                            '%route_name%' => $route->getName(),
                            '%route_id%' => $route->getId(),
                        ];
                        $this->logger->info(strtr(strip_tags($message), $params));
                        $this->output->writeln(strtr($message, $params));
                    }
                    $route->addStarredBy($athlete);
                    $publicStarred++;
                    $syncedIds[] = $routeData->id;
                }

                if ($route->isNew()) {
                    $message = '- Added new route: <info>%route_name%</info> (%route_id%)';
                    $params = [
                        '%route_name%' => $route->getName(),
                        '%route_id%' => $route->getId(),
                    ];
                    $this->logger->info(strtr(strip_tags($message), $params));
                    $this->output->writeln(strtr($message, $params));
                    $publicAdded++;
                } else {
                    $publicUpdated++;
                }
            } else {
                if ($this->routeService->exists($routeData->id)) {
                    $this->routeService->delete($routeData->id);
                    $privateDeleted++;
                }
                $privateSkipped++;
            }
        }

        $publicDeleted = $this->routeService->deleteAthleteRoutes($athlete, $syncedIds);
        $unstarred = $this->routeService->unstarAthleteRoutes($athlete, $syncedIds);

        $athlete->setLastSync(new \DateTime());
        $this->entityManager->flush();

        $message = '- Routes synced: 
  - public added: <info>%public_added%</info>
  - public updated: <info>%public_updated%</info>
  - public starred: <info>%public_starred%</info> 
  - public unstarred: <info>%unstarred%</info> 
  - public deleted: <info>%public_deleted%</info> 
  - published: <info>%published%</info> 
  - private skipped: <info>%private_skipped%</info>
  - private deleted: <info>%private_deleted%</info>';
        $params = [
            '%public_added%' => $publicAdded,
            '%public_updated%' => $publicUpdated,
            '%public_starred%' => $publicStarred,
            '%unstarred%' => $unstarred,
            '%public_deleted%' => $publicDeleted,
            '%published%' => $published,
            '%private_skipped%' => $privateSkipped,
            '%private_deleted%' => $privateDeleted,
        ];
        $this->logger->debug(strtr(strip_tags($message), $params));
        $this->output->writeln(strtr($message, $params));
    }
}
