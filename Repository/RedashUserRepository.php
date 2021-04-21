<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\RedashUser;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RedashUser|null find($id, $lockMode = null, $lockVersion = null)
 * @method RedashUser|null findOneBy(array $criteria, array $orderBy = null)
 * @method RedashUser[]    findAll()
 * @method RedashUser[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class RedashUserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RedashUser::class);
    }
}
