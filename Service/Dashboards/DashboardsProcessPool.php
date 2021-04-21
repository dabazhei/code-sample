<?php

declare(strict_types=1);

namespace App\Service\Dashboards;

use App\Builder\Process\ProcessBuilder;
use App\Command\SyncWidgetsCommand;
use App\Service\RedashProvider\RedashSourceWebService;
use Doctrine\DBAL\DBALException;
use Doctrine\Persistence\ObjectRepository;
use Generator;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;

/**
 * Class CollectDashboards
 * @package App\Service\Dashboards
 */
final class DashboardsProcessPool
{
    /**
     * @var array
     */
    private array $dashboards = [];
    /**
     * @var RedashSourceWebService
     */
    private RedashSourceWebService $redashWebService;
    /**
     * @var ProcessBuilder
     */
    private ProcessBuilder $processBuilder;
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;
    /**
     * @var string
     */
    private string $projectDir;
    /**
     * @var SymfonyStyle
     */
    private SymfonyStyle $io;

    /**
     * CollectDashboards constructor.
     * @param LoggerInterface $consoleLogger
     * @param RedashSourceWebService $redashWebService
     * @param string $projectDir
     */
    public function __construct(
        LoggerInterface $consoleLogger,
        RedashSourceWebService $redashWebService,
        string $projectDir
    ) {
        $this->redashWebService = $redashWebService;
        $this->processBuilder = new ProcessBuilder();
        $this->projectDir = $projectDir;
        $this->logger = $consoleLogger;
    }

    /**
     * @return array|null
     */
    private function getDashboards(): ?array
    {
        try {
            $this->dashboards = $this->redashWebService->getAllDashboards();
        } catch (ExceptionInterface $e) {
            $this->logger->error($e);
            $this->io->error($e->getMessage());
            return null;
        }
        return $this->dashboards;
    }

    /**
     * @param ObjectRepository $dashboardRepository
     * @return bool
     */
    public function archiveDashboards(ObjectRepository $dashboardRepository): bool
    {
        $redashDashboardsIds = array_column($this->getDashboards(), 'id');
        $dashboardsIds = array_column($dashboardRepository->findAllAsArray(), 'test_redash_id');
        $dashboardsToArchive = array_diff($dashboardsIds, $redashDashboardsIds);

        if (!empty($dashboardsToArchive)) {
            try {
                $dashboardRepository->archiveDashboards($dashboardsToArchive);
                return true;
            } catch (DBALException $DBALException) {
                $this->logger->error($DBALException);
                $this->io->error($DBALException->getMessage());
                return false;
            }
        }
        return true;
    }

    /**
     * @return Generator|null
     */
    public function create(): ?Generator
    {
        if ($this->dashboards === []) {
            $this->logger->error('No dashboards for process');
            $this->io->error('No dashboards for process');
            return null;
        }

        foreach ($this->dashboards as $dashboardData) {
            yield $this->processBuilder
                ->setCommandName(SyncWidgetsCommand::$defaultName)
                ->setParams('--slug=' . $dashboardData['slug'])
                ->createProcess()
                ->setWorkingDirectory($this->projectDir)
                ->build();
        }
    }

    /**
     * @param Generator $generator
     * @return bool
     */
    public function run(Generator $generator): bool
    {
        foreach ($generator as $process) {
            try {
                $process->mustRun();
                $message = 'Run process - ' . $process->getCommandLine();
                $this->io->success($message);
                $this->logger->info($message);
            } catch (ProcessFailedException $exception) {
                $message = 'Error in ' . $process->getCommandLine() . ' ' . $exception->getMessage();
                $this->io->error($message);
                $this->logger->error($message);
            }
        }

        $this->io->success('success synchronization');
        $this->logger->info('success synchronization');
        return true;
    }

    /**
     * @param SymfonyStyle $io
     * @return DashboardsProcessPool
     */
    public function setIo(SymfonyStyle $io): DashboardsProcessPool
    {
        $this->io = $io;
        return $this;
    }
}
