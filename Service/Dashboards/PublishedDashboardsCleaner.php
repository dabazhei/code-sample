<?php

declare(strict_types=1);

namespace App\Service\Dashboards;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\ConnectionException;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Throwable;

/**
 * Class PublishedDashboardsCleaner
 * @package App\Service\Dashboards
 */
final class PublishedDashboardsCleaner
{
    /**
     * @var Connection
     */
    private Connection $connection;

    /**
     * PublishedDashboardsCleaner constructor.
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->connection = $entityManager->getConnection();
    }

    /**
     * @return PublishedDashboardsCleaner
     */
    public function beginTransaction(): PublishedDashboardsCleaner
    {
        $this->connection->beginTransaction();
        return $this;
    }

    /**
     * @return void
     * @throws ConnectionException
     */
    public function endTransaction(): void
    {
        $this->connection->commit();
    }

    /**
     * @throws ConnectionException
     */
    public function rollbackTransaction(): void
    {
        $this->connection->rollBack();
    }

    /**
     * @return PublishedDashboardsCleaner
     * @throws Throwable
     */
    public function clearDashboardWidgetRelation(): PublishedDashboardsCleaner
    {
        $sql = 'delete from dashboard_widget dw
            where dw.widget_id in (
                select w.id
                from widget w, publisheddashboard pb
                where w.publishedboard_id = pb.id
            );';

        $this->run($sql);

        return $this;
    }

    /**
     * @return PublishedDashboardsCleaner
     * @throws Throwable
     */
    public function clearWidgets(): PublishedDashboardsCleaner
    {
        $sql = 'delete from widget w
            where w.publishedboard_id in (
                select pb.id
                from publisheddashboard pb
            );';

        $this->run($sql);

        return $this;
    }

    /**
     * @return PublishedDashboardsCleaner
     * @throws Throwable
     */
    public function clearVisualizations(): PublishedDashboardsCleaner
    {
        $sql = 'delete from visualization v
            where v.prod_redash_id is not null
            and v.id not in (select visualization_id from widget);';

        $this->run($sql);

        return $this;
    }

    /**
     * @return PublishedDashboardsCleaner
     * @throws Throwable
     */
    public function clearQueries(): PublishedDashboardsCleaner
    {
        $sql = 'delete from query q
            where q.prod_redash_id is not null
            and q.id not in (select query_id from visualization);';

        $this->run($sql);

        return $this;
    }

    /**
     * @return PublishedDashboardsCleaner
     * @throws Throwable
     */
    public function clearPublishedDashboards(): PublishedDashboardsCleaner
    {
        $sql = 'delete from publisheddashboard where 1 = 1;';

        $this->run($sql);

        return $this;
    }

    /**
     * @param string $sql
     * @throws Throwable
     */
    private function run(string $sql): void
    {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();
    }
}