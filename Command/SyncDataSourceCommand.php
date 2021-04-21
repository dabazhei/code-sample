<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\DataSource;
use App\Repository\DataSourceRepository;
use App\Service\DataSource\Retailers;
use App\Service\RedashProvider\RedashSourceWebService;
use App\Service\RedashProvider\RedashTargetWebService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;

final class SyncDataSourceCommand extends Command
{
    /**
     * @var string
     */
    public static $defaultName = 'app:sync-datasource';
    /**
     * @var RedashSourceWebService
     */
    private RedashSourceWebService $redashSourceWebService;
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;
    /**
     * @var RedashTargetWebService
     */
    private RedashTargetWebService $redashTargetWebService;
    /**
     * @var ContainerInterface
     */
    private ContainerInterface $container;
    /**
     * @var Retailers
     */
    private Retailers $retailersService;

    /**
     * SyncWidgetsCommand constructor.
     * @param LoggerInterface $logger
     * @param RedashSourceWebService $redashSourceWebService
     * @param RedashTargetWebService $redashTargetWebService
     * @param EntityManagerInterface $entityManager
     * @param ContainerInterface $container
     * @param Retailers $retailersService
     * @param string|null $name
     */
    public function __construct(
        LoggerInterface $logger,
        RedashSourceWebService $redashSourceWebService,
        RedashTargetWebService $redashTargetWebService,
        EntityManagerInterface $entityManager,
        ContainerInterface $container,
        Retailers $retailersService,
        string $name = null
    ) {
        $this->redashSourceWebService = $redashSourceWebService;
        $this->redashTargetWebService = $redashTargetWebService;
        $this->entityManager = $entityManager;
        $this->container = $container;
        $this->logger = $logger;
        $this->retailersService = $retailersService;

        parent::__construct($name);
    }

    /**
     * @return void
     */
    protected function configure(): void
    {
        $this->setDescription('Redash data source synchronization');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws ExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $dataSources = $this->redashSourceWebService->getDataSources();
        /** @var DataSourceRepository $repository */
        $repository = $this->entityManager->getRepository(DataSource::class);
        foreach ($dataSources as $data) {
            $dataSource = $repository->findOrCreateByRedashId($data['id']);
            $dataSource->fromArray($data);
            $dataSource->setRetailer($this->retailersService->findRetailerNameInString($data['name']));
            if (null === $dataSource->getRetailer()) {
                $this->logger->error("DataSource for unconfigured retailer: {$data['name']}");
                continue;
            }
            try {
                $this->entityManager->persist($dataSource);
                $io->success('DataSource ID: ' . $dataSource->getTestRedashId() . ' successfully saved');
                $this->logger->info('DataSource ID: ' . $dataSource->getTestRedashId() . ' successfully saved');
            } catch (Exception $e) {
                $message = 'DataSource not created: ' . $e->getMessage();
                $io->error($message);
                $this->logger->error($message);
            }
        }

        $this->entityManager->flush();

        $io->success('DataSources successfully saved');
        $this->logger->info('DataSources successfully saved');

        return Command::SUCCESS;
    }
}
