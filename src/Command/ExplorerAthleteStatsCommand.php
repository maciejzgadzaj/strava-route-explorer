<?php

namespace App\Command;

use App\Entity\Athlete;
use App\Entity\Route;
use App\Service\AthleteService;
use App\Service\MapService;
use App\Service\RouteService;
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
class ExplorerAthleteStatsCommand extends ContainerAwareCommand
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
     * @var \App\Service\StravaService
     */
    private $stravaService;

    /**
     * @var int
     */
    private $athleteId;

    /**
     * ExplorerAthleteStatsCommand constructor.
     *
     * @param string|null $name
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param \App\Service\RouteService $routeService
     * @param \App\Service\MapService $mapService
     * @param \App\Service\AthleteService $athleteService
     * @param \App\Service\StravaService $stravaService
     */
    public function __construct(
        string $name = null,
        EntityManagerInterface $entityManager,
        RouteService $routeService,
        MapService $mapService,
        AthleteService $athleteService,
        StravaService $stravaService
    ) {
        parent::__construct($name);

        $this->entityManager = $entityManager;
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
        $this->setName('explorer:athlete:stats')
            ->setDescription('Route stats for an athlete.')
            ->addOption(
                'athlete',
                null,
                InputOption::VALUE_REQUIRED
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
        $athlete = $this->athleteService->load($this->athleteId);

        $options = [
            'headers' => [
                'Authorization' => 'Bearer '.$athlete->getAccessToken(),
            ],
        ];
        $response = $this->stravaService->apiRequest(
            'get',
            '/api/v3/athletes/'.$athlete->getId().'/routes?per_page=200',
            $options
        );
        $content = \GuzzleHttp\json_decode($response->getBody()->getContents());

        $stats = [
            'athlete' => [
                'all' => 0,
                'public' => 0,
                'private' => 0,
                'starred' => 0,
            ],
            'other_athlete' => [
                'all' => 0,
                'public' => 0,
                'private' => 0,
                'starred' => 0,
            ],
            'type' => [
                1 => 0,
                2 => 0,
            ],
        ];

        foreach ($content as $route) {
            if ($route->starred) {
                print '* ';
            }

            if ($route->private) print '[ ';
            print($route->id.' - '.$route->name);
            if ($route->private) print ' ]';
            $stats['type'][$route->type]++;

            if ($route->athlete->id == $athlete->getId()) {
                $stats['athlete']['all']++;
                if ($route->private) {
                    $stats['athlete']['private']++;
                } else {
                    $stats['athlete']['public']++;
                }
                if ($route->starred) {
                    $stats['athlete']['starred']++;
                }
            } else {
                print " ----- by {$route->athlete->firstname} {$route->athlete->lastname}";
                $stats['other_athlete']['all']++;
                if ($route->private) {
                    $stats['other_athlete']['private']++;
                } else {
                    $stats['other_athlete']['public']++;
                }
                if ($route->starred) {
                    $stats['other_athlete']['starred']++;
                }
            }
            print "\n";
        }
        dump($stats);
    }
}
