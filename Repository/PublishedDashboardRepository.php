<?php

namespace App\Repository;

use App\Entity\PublishedDashboard;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PublishedDashboard|null find($id, $lockMode = null, $lockVersion = null)
 * @method PublishedDashboard|null findOneBy(array $criteria, array $orderBy = null)
 * @method PublishedDashboard[]    findAll()
 * @method PublishedDashboard[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PublishedDashboardRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PublishedDashboard::class);
    }
}
