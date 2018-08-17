<?php

namespace App\Command;

use App\Service\MeetupService;
use App\Service\RouteService;
use App\Service\StravaService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ExplorerMeetupScrapCommand
 *
 * @package App\Command
 */
class ExplorerMeetupScrapCommand extends ContainerAwareCommand
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
    private $meetupGroups;

    /**
     * @var string
     */
    private $startDate;

    /**
     * @var string
     */
    private $endDate;

    /**
     * @var int
     */
    private $addedRoutes = 0;

    /**
     * ExplorerMeetupScrapCommand constructor.
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
        $this->setName('explorer:meetup:scrap')
            ->setDescription('Scraps routes from Meetup events and their comments.')
            ->addOption(
                'group',
                null,
                InputOption::VALUE_OPTIONAL,
                'Meetup group(s) to scrap (multiple values separated by comma)'
            )
            ->addOption(
                'start_date',
                null,
                InputOption::VALUE_OPTIONAL,
                'Start date (in <comment>Y-m-d</comment> format)'
            )
            ->addOption(
                'end_date',
                null,
                InputOption::VALUE_OPTIONAL,
                'End date (in <comment>Y-m-d</comment> format)'
            )
            ;
    }

    /**
     * {@inheritdoc}
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);

        if ($groups = $input->getOption('group', '')) {
            $this->meetupGroups = explode(',', $groups);
        }

        $this->startDate = $input->getOption('start_date', null);
        $this->endDate = $input->getOption('end_date', null);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        $this->logger->log('debug', 'Meetup route scraper: start');
        $output->writeln('Starting...');

        foreach ($this->meetupGroups as $group) {
            $this->logger->log('debug', 'Meetup route scraper: processing group: '.$group);
            $output->writeln("* Processing group <info>$group</info>");
            $uri = '/'.$group.'/events';

            $query = $status = [];
            if ($this->startDate) {
                $startTime = strtotime($this->startDate);
                $query['no_earlier_than'] = date('Y-m-d', $startTime);
                if ($startTime < time()) {
                    $status[] = 'past';
                }
            }
            if ($this->startDate) {
                $endTime = strtotime($this->endDate.' + 1 day');
                $query['no_later_than'] = date('Y-m-d', $endTime);
                if ($endTime > time()) {
                    $status[] = 'upcoming';
                }
            }
            if ($status) {
                $query['status'] = implode(',', $status);
            }
            if ($query) {
                $uri .= '?'.http_build_query($query);
            }

            $output->writeln("  - Fetching events...");
            $response = $this->meetupService->apiRequest('get', $uri);
            $content = \GuzzleHttp\json_decode($response->getBody()->getContents());

            foreach ($content as $event) {
                $params = [
                    '%event_id%' => $event->id,
                    '%event_name%' => $event->name,
                ];
                $this->logger->log('debug', strtr('Meetup route scraper: processing event %event_id%: %event_name%', $params));
                $output->writeln(strtr('  - Processing event %event_id%: <comment>%event_name%</comment>', $params));

                $this->checkEventDescription($event);
                $this->checkEventComments($group, $event);
            }
        }

        $this->logger->log('debug', 'Routes added: '.$this->addedRoutes);
        $output->writeln('Routes added: '.$this->addedRoutes);

        $this->logger->log('debug', 'Meetup route scraper: end');
        $output->writeln('All done.');
    }

    /**
     * Check even description for Strava route links.
     *
     * @param object $event
     */
    private function checkEventDescription($event)
    {
        if (!isset($event->description)) {
            return;
        }

        $matches = [];
        preg_match_all(self::ROUTE_PATTERN, $event->description, $matches);

        if (!empty($matches[1])) {
            foreach ($matches[1] as $routeId) {
                $route = $this->syncRoute($routeId);
            }
        }
    }

    /**
     * Check event comments for Strava route links.
     *
     * @param object $group
     * @param object $event
     *
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    private function checkEventComments($group, $event)
    {
        $uri = '/'.$group.'/events/'.$event->id.'/comments';

        $response = $this->meetupService->apiRequest('get', $uri);
        $content = \GuzzleHttp\json_decode($response->getBody()->getContents());

        foreach ($content as $comment) {
            $this->checkSingleComment($comment);
        }
    }

    /**
     * Check single event comment for Strava route links.
     *
     * @param object $comment
     */
    private function checkSingleComment($comment)
    {
        if (!isset($comment->comment)) {
            return;
        }

        $matches = [];
        preg_match_all(self::ROUTE_PATTERN, $comment->comment, $matches);

        if (!empty($matches[1])) {
            foreach ($matches[1] as $routeId) {
                $route = $this->syncRoute($routeId);
            }
        }

        // Check all replies as well.
        if (!empty($comment->replies)) {
            foreach ($comment->replies as $reply) {
                $this->checkSingleComment($reply);
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
        // Sync only new routes.
        if (!$this->routeService->exists($routeId)) {
            if ($route = $this->routeService->syncRoute($routeId)) {
                $this->output->writeln(strtr('    <info>- Added route %route_id%: "%route_name%" by %athlete%</info>', [
                    '%route_name%' => $route->getName(),
                    '%route_id%' => $route->getId(),
                    '%athlete%' => $route->getAthlete()->getName(),
                ]));
            }

            $this->addedRoutes++;

            return $route;
        }
    }
}
