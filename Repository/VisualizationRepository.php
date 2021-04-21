<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Visualization;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Visualization|null find($id, $lockMode = null, $lockVersion = null)
 * @method Visualization|null findOneBy(array $criteria, array $orderBy = null)
 * @method Visualization[]    findAll()
 * @method Visualization[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class VisualizationRepository extends ServiceEntityRepository
{
    /**
     * VisualizationRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Visualization::class);
    }

    /**
     * @param int $redashId
     * @return Visualization
     */
    public function findOrCreate(int $redashId): Visualization
    {
        $visualization = $this->findOneBy(['testRedashId' => $redashId]);

        if ($visualization === null) {
            $visualization = new Visualization();
            $visualization->setTestRedashId($redashId);
        }

        return $visualization;
    }
}
