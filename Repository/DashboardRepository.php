<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Dashboard;
use App\VO\Dashboards\DashboardsRequestVO;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Exception;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Dashboard|null find($id, $lockMode = null, $lockVersion = null)
 * @method Dashboard|null findOneBy(array $criteria, array $orderBy = null)
 * @method Dashboard[]    findAll()
 * @method Dashboard[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class DashboardRepository extends ServiceEntityRepository
{

    /**
     * DashboardRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Dashboard::class);
    }

    /**
     * @param int $redashId
     * @return Dashboard
     */
    public function findOrCreate(int $redashId): Dashboard
    {
        $dashboard = $this->findOneBy(['testRedashId' => $redashId]);

        if ($dashboard === null) {
            $dashboard = new Dashboard();
            $dashboard->setTestRedashId($redashId);
        }

        return $dashboard;
    }

    /**
     * @return array
     * @throws DBALException
     * @throws Exception
     */
    public function getTags(): array
    {
        $connection = $this->getEntityManager()->getConnection();
        $sql = 'SELECT distinct (jsonb_array_elements_text(tags)) as tag FROM dashboard';
        $stmt = $connection->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * @param DashboardsRequestVO $dashboardsRequestVO
     * @return array
     * @throws Exception
     * @throws DBALException
     */
    public function findDashboards(DashboardsRequestVO $dashboardsRequestVO): array
    {
        $data = $dashboardsRequestVO->toArray();
        $connection = $this->getEntityManager()->getConnection();
        $sql = "SELECT id, name, updated, tags, is_active FROM dashboard WHERE is_archived = FALSE";

        if (empty($data)) {
            $stmt = $connection->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        }

        $bind = [];
        foreach (array_keys($data) as $key) { //todo Produce SQL query Builder.
            if ($key === 'name') {
                $sql .= " AND LOWER({$key}) LIKE LOWER(:{$key})";
                $data[$key] = '%' . $data[$key] . '%';
            } elseif ($key === 'tags') {
                $sql .= " AND jsonb_exists_any(tags, array [:{$key}])";
            } elseif ($key === 'active') {
                $sql .= " AND is_active = :{$key}";
            } else {
                $sql .= " AND {$key} = :{$key}";
            }

            if ($data[$key] === false) {
                $bind[$key] = 'False';
            } else {
                $bind[$key] = $data[$key] === true ? 'True' : $data[$key];
            }
        }

        $stmt = $connection->prepare($sql);
        $stmt->execute($bind);
        return $stmt->fetchAll();
    }

    /**
     * @param int $id
     * @param int $status
     * @return bool
     * @throws Exception
     * @throws DBALException
     */
    public function updateActiveStatus(int $id, int $status): bool
    {
        $connection = $this->getEntityManager()->getConnection();
        $sql = "UPDATE dashboard SET is_active = :status WHERE id = :id AND is_archived = FALSE";
        $stmt = $connection->prepare($sql);

        return $stmt->execute(
            [
                'status' => $status ? 1 : 0,
                'id' => $id
            ]
        );
    }

    /**
     * @return array
     * @throws Exception
     * @throws DBALException
     */
    public function findAllAsArray(): array
    {
        $connection = $this->getEntityManager()->getConnection();
        $sql = "SELECT * FROM dashboard WHERE is_archived=FALSE";
        $stmt = $connection->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * @param array $dashboardsIds
     * @return bool
     * @throws Exception
     * @throws DBALException
     */
    public function archiveDashboards(array $dashboardsIds): bool
    {
        $connection = $this->getEntityManager()->getConnection();
        $sql = "UPDATE dashboard SET is_archived = TRUE WHERE test_redash_id IN (:dashboardsIds)";
        $values = ['dashboardsIds' => array_values($dashboardsIds)];
        $types = ['dashboardsIds' => Connection::PARAM_INT_ARRAY];
        $stmt = $connection->executeQuery($sql, $values, $types);
        return empty($stmt->fetchAll());
    }
}
