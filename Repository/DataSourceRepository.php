<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\DataSource;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DataSource|null find($id, $lockMode = null, $lockVersion = null)
 * @method DataSource|null findOneBy(array $criteria, array $orderBy = null)
 * @method DataSource[]    findAll()
 * @method DataSource[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class DataSourceRepository extends ServiceEntityRepository
{
    /**
     * DataSourceRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DataSource::class);
    }

    /**
     * @param int $redashId
     * @return DataSource
     */
    public function findOrCreateByRedashId(int $redashId): DataSource
    {
        $dataSource = $this->findOneBy([
            'testRedashId' => $redashId,
            'prodRedashId' => null,
        ]);

        if ($dataSource === null) {
            $dataSource = new DataSource();
            $dataSource->setTestRedashId($redashId);
        }

        return $dataSource;
    }

    /**
     * @return DataSource[]
     */
    public function findAllOrigin(): array
    {
        return $this->findBy(['prodRedashId' => null]);
    }
}
