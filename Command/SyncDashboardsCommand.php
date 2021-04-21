<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Dashboard;
use App\Service\Dashboards\DashboardsProcessPool;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Generator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class SyncDashboardsCommand
 * @package App\Command
 */
final class SyncDashboardsCommand extends Command
{
    /**
     * @var string
     */
    public static $defaultName = 'app:sync-dashboards';
    /**
     * @var DashboardsProcessPool
     */
    private DashboardsProcessPool $dashboardsProcessPool;
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;

    /**
     * SyncDashboardsCommand constructor.
     * @param DashboardsProcessPool $dashboardsProcessPoll
     * @param EntityManagerInterface $entityManager
     * @param string|null $name
     */
    public function __construct(
        DashboardsProcessPool $dashboardsProcessPoll,
        EntityManagerInterface $entityManager,
        string $name = null
    ) {
        parent::__construct($name);
        $this->dashboardsProcessPool = $dashboardsProcessPoll;
        $this->entityManager = $entityManager;
    }

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->setDescription('Redash dashboards synchronization');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->dashboardsProcessPool->setIo(new SymfonyStyle($input, $output));
        $activationResult = $this->dashboardsProcessPool->archiveDashboards(
            $this->entityManager->getRepository(Dashboard::class)
        );

        $generator = $this->dashboardsProcessPool->create();
        if ($activationResult === true && ($generator !== null || ($generator instanceof Generator))) {
            $this->dashboardsProcessPool->run($generator);
            return Command::SUCCESS;
        }

        return Command::FAILURE;
    }
}
