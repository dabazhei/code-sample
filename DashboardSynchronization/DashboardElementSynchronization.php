<?php

declare(strict_types=1);

namespace App\DashboardSynchronization;

use App\Entity\Dashboard;
use App\Entity\Query;
use App\Entity\Visualization;
use App\Entity\Widget;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class DashboardSynchronization
 * @package App\DashboardSynchronization
 */
abstract class DashboardElementSynchronization implements SynchronizationInterface
{
    /**
     * @var SynchronizationInterface
     */
    protected SynchronizationInterface $next;
    /**
     * @var EntityManagerInterface
     */
    protected EntityManagerInterface $entityManager;
    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;
    /**
     * @var ObjectRepository
     */
    protected ObjectRepository $dashboardRepository;
    /**
     * @var ObjectRepository
     */
    protected ObjectRepository $visualizationRepository;
    /**
     * @var ObjectRepository
     */
    protected ObjectRepository $widgetRepository;
    /**
     * @var ObjectRepository
     */
    protected ObjectRepository $queryRepository;
    /**
     * @var ContainerInterface
     */
    protected ContainerInterface $container;

    /**
     * DashboardElementSynchronization constructor.
     * @param EntityManagerInterface $entityManager
     * @param LoggerInterface $consoleLogger
     * @param ContainerInterface $container
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        LoggerInterface $consoleLogger,
        ContainerInterface $container
    )
    {
        $this->container = $container;
        $this->entityManager = $entityManager;
        $this->logger = $consoleLogger;
        $this->dashboardRepository = $this->entityManager->getRepository(Dashboard::class);
        $this->widgetRepository = $this->entityManager->getRepository(Widget::class);
        $this->visualizationRepository = $this->entityManager->getRepository(Visualization::class);
        $this->queryRepository = $this->entityManager->getRepository(Query::class);
    }

    /**
     * @param SynchronizationInterface $dashboardSynchronization
     */
    public function setNext(SynchronizationInterface $dashboardSynchronization): void
    {
        $this->next = $dashboardSynchronization;
    }

    /**
     * @param array $dashboardData
     */
    abstract public function synchronize(array $dashboardData): void;
}