<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Widget;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Widget|null find($id, $lockMode = null, $lockVersion = null)
 * @method Widget|null findOneBy(array $criteria, array $orderBy = null)
 * @method Widget[]    findAll()
 * @method Widget[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class WidgetRepository extends ServiceEntityRepository
{
    /**
     * WidgetRepository constructor.
     * @param ManagerRegistry $registry
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Widget::class);
    }

    /**
     * @param int $testRedashId
     * @return Widget
     */
    public function findOrCreate(int $testRedashId): Widget
    {
        $widget = $this->findOneBy(['testRedashId' => $testRedashId]);

        if ($widget === null) {
            $widget = new Widget();
            $widget->setTestRedashId($testRedashId);
        }

        return $widget;
    }
}
