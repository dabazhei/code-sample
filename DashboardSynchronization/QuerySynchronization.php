<?php

declare(strict_types=1);

namespace App\DashboardSynchronization;

use App\Entity\DataSource;
use App\Entity\Query;
use App\Entity\Visualization;
use App\Service\QueryService;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;

/**
 * Class QuerySynchronization
 * @package App\DashboardSynchronization
 */
final class QuerySynchronization extends DashboardElementSynchronization
{
    /**
     * @param array $visualizationData
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws ExceptionInterface
     */
    public function synchronize(array $visualizationData): void
    {
        $query = $this->queryRepository->findOrCreate(
            $visualizationData['query']['id']
        );

        $dataSourceRepo = $this->entityManager->getRepository(DataSource::class);
        $dataSource = $dataSourceRepo->findOneBy(['testRedashId' => $visualizationData['query']['data_source_id']]);
        $query
            ->fromArray($visualizationData['query'])
            ->setDataSource($dataSource)
        ;
        $this->entityManager->persist($query);
        $visualization = $this->visualizationRepository->findOrCreate(
            $visualizationData['id']
        );

        $visualization->setQuery($query);
        $this->entityManager->flush();
    }
}
