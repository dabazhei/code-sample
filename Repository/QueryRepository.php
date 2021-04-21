<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Query;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\DBALException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Query|null find($id, $lockMode = null, $lockVersion = null)
 * @method Query|null findOneBy(array $criteria, array $orderBy = null)
 * @method Query[]    findAll()
 * @method Query[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class QueryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Query::class);
    }

    /**
     * @param int $testRedashId
     * @return Query
     */
    public function findOrCreate(int $testRedashId): Query
    {
        $query = $this->findOneBy(['testRedashId' => $testRedashId]);

        if ($query === null) {
            $query = new Query();
            $query->setTestRedashId($testRedashId);
        }

        return $query;
    }

    /**
     * @param int $testOrProdId
     * @return Query|null
     */
    public function findPublishedOrNot(int $testOrProdId): ?Query
    {
        $qb = $this->createQueryBuilder('q');
        $qb->where('q.testRedashId = :id')
            ->orWhere('q.prodRedashId = :id')
            ->setParameter('id', $testOrProdId);

        $result = $qb->getQuery()->getResult();

        return !empty($result) ? $result[0] : null;
    }
}
