<?php

declare(strict_types=1);

namespace App\DashboardSynchronization;

use App\Entity\Widget;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class WidgetSynchronization
 * @package App\DashboardSynchronization
 */
final class WidgetSynchronization extends DashboardElementSynchronization
{
    /**
     * WidgetSynchronization constructor.
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
        $this->next = new VisualizationSynchronization($this->entityManager, $this->logger, $this->container);
    }

    /**
     * @param array $dashboardData
     */
    public function synchronize(array $dashboardData): void
    {
        foreach ($dashboardData['widgets'] as $widgetData) {
            /** @var Widget $widget */
            $widget = $this->widgetRepository->findOrCreate($widgetData['id']);
            $widgetData['dashboard'] = $this->dashboardRepository->findOneBy(
                ['testRedashId' => $widgetData['dashboard_id']]
            );

            $widget->fromArray($widgetData);
            $this->entityManager->persist($widget);
            $this->entityManager->flush();

            if (isset($widgetData['visualization']['id'])) {
                $this->next->synchronize($widgetData);
            }
        }
    }
}
