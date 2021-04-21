<?php

declare(strict_types=1);

namespace App\DashboardSynchronization;

/**
 * Interface SynchronizationInterface
 * @package App\DashboardSynchronization
 */
interface SynchronizationInterface
{
    /**
     * @param SynchronizationInterface $dashboardSynchronization
     */
    public function setNext(SynchronizationInterface $dashboardSynchronization): void;

    /**
     * @param array $dashboardData
     */
    public function synchronize(array $dashboardData): void;
}
