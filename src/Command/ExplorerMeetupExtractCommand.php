<?php

namespace App\Command;

use App\Service\MeetupService;
use App\Service\RouteService;
use App\Service\StravaService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ExplorerMeetupExtractCommand
 *
 * @package App\Command
 */
class ExplorerMeetupExtractCommand extends ContainerAwareCommand
{
    const ROUTE_PATTERN = '/https:\/\/www\.strava\.com\/routes\/(\d+)/i';
    
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \App\Service\MeetupService
     */
    private $meetupService;

    /**
     * @var \App\Service\StravaService
     */
    private $stravaService;

    /**
     * @var \App\Service\RouteService
     */
    private $routeService;

    /**
     * @var array
     */
    private $meetupGroups;

    /**
     * ExplorerMeetupExtractCommand constructor.
     *
     * @param string|null $name
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param \Psr\Log\LoggerInterface $logger
     * @param \App\Service\MeetupService $meetupService
     * @param \App\Service\StravaService $stravaService
     * @param \App\Service\RouteService $routeService
     * @param array $meetupGroups
     */
    public function __construct(
        string $name = null,
        EntityManagerInterface $entityManager,
        LoggerInterface $logger,
        MeetupService $meetupService,
        StravaService $stravaService,
        RouteService $routeService,
        $meetupGroups
    ) {
        parent::__construct($name);

        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->meetupService = $meetupService;
        $this->stravaService = $stravaService;
        $this->routeService = $routeService;
        $this->meetupGroups = $meetupGroups;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('explorer:meetup:extract')
            ->setDescription('Fetch routes from Meetup.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->logger->log('debug', 'Meetup route extractor: start');
        $output->writeln('Starting...');

        foreach ($this->meetupGroups as $group) {
            $this->logger->log('debug', 'Meetup route extractor: processing group: '.$group);
            $output->writeln("<info>* Processing group <comment>$group</comment></info>");
            $uri = '/'.$group.'/events';

            $response = $this->meetupService->apiRequest('get', $uri);
            $content = \GuzzleHttp\json_decode($response->getBody()->getContents());

            foreach ($content as $event) {
                $this->logger->log('debug', 'Meetup route extractor: processing event: '.$event->name);
                $output->writeln("  <info>- Processing event <comment>{$event->name}</comment></info>");

                $this->checkEventDescription($event, $input, $output);
                $this->checkEventComments($group, $event, $input, $output);
            }
        }

        $this->logger->log('debug', 'Meetup route extractor: end');
        $output->writeln('All done.');
    }

    /**
     * Check even description for Strava route links.
     *
     * @param $event
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    private function checkEventDescription($event, InputInterface $input, OutputInterface $output)
    {
        if (!isset($event->description)) {
            return;
        }

        $matches = [];
        preg_match_all(self::ROUTE_PATTERN, $event->description, $matches);

        if (!empty($matches[1])) {
            foreach ($matches[1] as $routeId) {
                if ($route = $this->syncRoute($routeId)) {
                    $output->writeln(strtr('    - Synced route <comment>%route_name%</comment> (%route_id%)', [
                        '%route_name%' => $route->getName(),
                        '%route_id%' => $route->getId(),
                    ]));
                }
            }
        }
    }

    /**
     * Check event comments for Strava route links.
     *
     * @param $group
     * @param $event
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function checkEventComments($group, $event, InputInterface $input, OutputInterface $output)
    {
        $uri = '/'.$group.'/events/'.$event->id.'/comments';

        $response = $this->meetupService->apiRequest('get', $uri);
        $content = \GuzzleHttp\json_decode($response->getBody()->getContents());

        foreach ($content as $comment) {
            $this->checkSingleComment($comment, $input, $output);
        }
    }

    /**
     * Check single event comment for Strava route links.
     *
     * @param $comment
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     */
    private function checkSingleComment($comment, InputInterface $input, OutputInterface $output)
    {
        if (!isset($comment->comment)) {
            return;
        }

        $matches = [];
        preg_match_all(self::ROUTE_PATTERN, $comment->comment, $matches);

        if (!empty($matches[1])) {
            foreach ($matches[1] as $routeId) {
                if ($route = $this->syncRoute($routeId)) {
                    $output->writeln(strtr('    - Synced route <comment>%route_name%</comment> (%route_id%)', [
                        '%route_name%' => $route->getName(),
                        '%route_id%' => $route->getId(),
                    ]));
                }
            }
        }

        // Check all replies as well.
        if (!empty($comment->replies)) {
            foreach ($comment->replies as $reply) {
                $this->checkSingleComment($reply, $input, $output);
            }
        }
    }

    /**
     * Sync single route if it doesn't exist locally
     * .
     * @param $routeId
     *
     * @return \App\Entity\Route|null
     */
    private function syncRoute($routeId)
    {
        // Sync only non-existent routes.
        if (!$this->routeService->exists($routeId)) {
            return $this->routeService->syncRoute($routeId);
        }
    }
}
