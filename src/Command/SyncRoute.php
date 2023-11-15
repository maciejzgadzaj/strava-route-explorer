<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\RouteService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'sync-route')]
class SyncRoute extends Command
{
    public function __construct(
        private readonly RouteService $routeService,
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this->addArgument(
            'route_id',
            InputArgument::REQUIRED,
            'Route ID',
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->routeService->syncRoute(
            (int) $input->getArgument('route_id'),
        );

        return Command::SUCCESS;
    }
}
