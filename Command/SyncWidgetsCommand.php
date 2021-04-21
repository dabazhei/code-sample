<?php

declare(strict_types=1);

namespace App\Command;

use App\DashboardSynchronization\DashboardSynchronization;
use App\Service\RedashProvider\RedashSourceWebService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;

/**
 * Class SyncWidgetsCommand
 * @package App\Command
 */
final class SyncWidgetsCommand extends Command
{
    /**
     * @var string
     */
    public static $defaultName = 'app:sync-widgets';
    /**
     * @var RedashSourceWebService
     */
    private RedashSourceWebService $redashWebService;
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;
    /**
     * @var DashboardSynchronization
     */
    private DashboardSynchronization $dashboardSynchronization;

    /**
     * SyncWidgetsCommand constructor.
     * @param LoggerInterface $consoleLogger
     * @param RedashSourceWebService $redashWebService
     * @param DashboardSynchronization $dashboardSynchronization
     * @param string|null $name
     */
    public function __construct(
        LoggerInterface $consoleLogger,
        RedashSourceWebService $redashWebService,
        DashboardSynchronization $dashboardSynchronization,
        string $name = null
    ) {
        parent::__construct($name);
        $this->redashWebService = $redashWebService;
        $this->logger = $consoleLogger;
        $this->dashboardSynchronization = $dashboardSynchronization;
    }

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setDescription('Redash widgets synchronization')
            ->addOption(
                'slug',
                null,
                InputOption::VALUE_OPTIONAL,
                'Dashboard slug',
                false
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws ExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $dashboardSlug = $input->getOption('slug');
        if ($dashboardSlug === false) {
            $this->logger->error('You must provide a slug');
            return Command::FAILURE;
        }

        $this->dashboardSynchronization->synchronize(
            $this->redashWebService->getDashboardDataBySlug($dashboardSlug)
        );
        return Command::SUCCESS;
    }
}
