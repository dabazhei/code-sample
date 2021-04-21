<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\Dashboards\PublishedDashboardsCleaner;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

final class ClearPublishedBoardsCommand extends Command
{
    /**
     * @var string
     */
    public static $defaultName = 'app:clear-published-boards';
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;
    /**
     * @var PublishedDashboardsCleaner
     */
    private PublishedDashboardsCleaner $publishedDashboardsCleaner;

    /**
     * SyncWidgetsCommand constructor.
     * @param LoggerInterface $logger
     * @param PublishedDashboardsCleaner $publishedDashboardsCleaner
     * @param string|null $name
     */
    public function __construct(
        LoggerInterface $logger,
        PublishedDashboardsCleaner $publishedDashboardsCleaner,
        string $name = null
    ) {
        $this->publishedDashboardsCleaner = $publishedDashboardsCleaner;
        $this->logger = $logger;

        parent::__construct($name);
    }

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->setDescription('Clear Published Boards and related board elements');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $this->publishedDashboardsCleaner
                ->beginTransaction()
                ->clearDashboardWidgetRelation()
                ->clearWidgets()
                ->clearVisualizations()
                ->clearQueries()
                ->clearPublishedDashboards()
                ->endTransaction()
            ;
        } catch (Throwable $e) {
            $this->publishedDashboardsCleaner->rollbackTransaction();
            $message = 'Published boards not cleared: ' . $e->getMessage();
            $io->error($message);
            $this->logger->error($message);

            return Command::FAILURE;
        }

        $io->success('Published boards successfully cleared');
        $this->logger->info('Published boards successfully cleared');

        return Command::SUCCESS;
    }
}
