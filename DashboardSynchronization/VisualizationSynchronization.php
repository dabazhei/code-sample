<?php

declare(strict_types=1);

namespace App\DashboardSynchronization;

use App\Entity\Visualization;
use App\Entity\Widget;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class VisualizationSynchronization
 * @package App\DashboardSynchronization
 */
final class VisualizationSynchronization extends DashboardElementSynchronization
{
    /**
     * VisualizationSynchronization constructor.
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
        $this->next = new QuerySynchronization($this->entityManager, $this->logger, $this->container);
    }

    /**
     * @param array $widgetData
     */
    public function synchronize(array $widgetData): void
    {
        $visualization = $this->visualizationRepository->findOrCreate(
            $widgetData['visualization']['id']
        );

        $widget = $this->widgetRepository->findOrCreate(
            $widgetData['id']
        );

        $visualization->fromArray($widgetData['visualization']);
        $widget->setVisualization($visualization);
        $this->entityManager->persist($widget);
        $this->entityManager->persist($visualization);
        $this->entityManager->flush();

       if (isset($widgetData['visualization']['query']['id'])) {
           $this->next->synchronize($widgetData['visualization']);
       }
    }
}
