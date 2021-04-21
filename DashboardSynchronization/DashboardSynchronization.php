<?php

declare(strict_types=1);

namespace App\DashboardSynchronization;

use App\Entity\Dashboard;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class DashboardSynchronization
 * @package App\DashboardSynchronization
 */
final class DashboardSynchronization extends DashboardElementSynchronization
{
    /**
     * DashboardSynchronization constructor.
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
        parent::__construct($entityManager, $consoleLogger, $container);
        $this->next = new WidgetSynchronization($this->entityManager, $this->logger, $this->container);
    }

    /**
     * @param array $dashboardData
     */
    public function synchronize(array $dashboardData): void
    {
        $this->logger->info('start ' . self::class . 'with name : ' . $dashboardData['name']);
        $dashboardRepository = $this->entityManager->getRepository(Dashboard::class);

        /** @var Dashboard $dashboard */
        $dashboard = $dashboardRepository->findOrCreate($dashboardData['id']);
        $lastUpdated = strtotime($dashboardData['updated_at'] ?? '');
        if ($dashboard->getUpdated() < $lastUpdated) {
            foreach ($dashboard->getPublishedDashboards() as $publishedDashboard) {
                $dashboard->removePublishedDashboard($publishedDashboard);
                $this->entityManager->remove($publishedDashboard);
            }
        }
        $dashboard->fromArray($dashboardData);
        $this->entityManager->persist($dashboard);
        $this->entityManager->flush();

        if (!empty($dashboardData['widgets'])) {
            $this->next->synchronize($dashboardData);
        }
    }
}
