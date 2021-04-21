<?php

declare(strict_types=1);

namespace App\DashboardCreationSteps;

use App\Entity\DataSource;
use App\Exception\DataSourceCreationException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;

/**
 * Class CreateDataSource
 * @package App\DashboardCreationSteps
 */
class CreateDataSource
{
    /**
     * @param Payload $payload
     * @return Payload
     * @throws ExceptionInterface
     */
    public function __invoke(Payload $payload)
    {
        $dataSourceRepository = $payload->getEntityManager()->getRepository(DataSource::class);
        $dataSources = $dataSourceRepository->findAllOrigin();

        if (empty($dataSources)) {
            $payload->getLogger()->error("Cant find data sources for dashboard id - {$payload->getDashboardId()} ");
            throw new DataSourceCreationException("Cant find data sources for dashboard id - {$payload->getDashboardId()} ");
        }

        foreach ($dataSources as $dataSource) {
            /** @var DataSource $newDataSource */
            $newDataSource = clone $dataSource;
            $newDataSourceId = $payload->getRedashWebService()->createDataSource($dataSource, $payload->getContainer());
            $newDataSource
                ->setProdRedashId($newDataSourceId)
                ->setRedashUser($payload->getRedashUser())
            ;
            $payload->getEntityManager()->persist($newDataSource);
            $payload->getRedashUser()->addDataSource($newDataSource);
        }
        $payload->getEntityManager()->persist($payload->getRedashUser());
        $payload->getEntityManager()->flush();

        return $payload;
    }
}