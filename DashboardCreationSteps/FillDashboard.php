<?php

declare(strict_types=1);

namespace App\DashboardCreationSteps;

use App\Entity\DataSource;
use App\Entity\Query;
use App\Entity\RedashUser;
use App\Exception\DashboardCreationException;
use App\Exception\QueryCreationException;
use App\Service\QueryService;
use Doctrine\ORM\ORMException;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;

class FillDashboard
{
    /**
     * @param Payload $payload
     * @return Payload
     * @throws ExceptionInterface
     * @throws ORMException
     */
    public function __invoke(Payload $payload)
    {
        /** @var QueryService $queryService */
        $queryService = $payload->getServiceContainer()->get(QueryService::class);
        $entityManager = $payload->getEntityManager();
        $dashboard = $payload->getDashboard();
        foreach ($dashboard->getWidget() as $widgetOrigin) {
            $visualizationOrigin = $widgetOrigin->getVisualization();

            if ($visualizationOrigin === null) {
                $message = "Widget with id {$widgetOrigin->getId()} has no visualization";
                $payload->getLogger()->debug($message);
                throw new DashboardCreationException($message);
            }

            $queryNew = clone $visualizationOrigin->getQuery();

            if ($queryNew === null) {
                $message = "Visualization with id {$visualizationOrigin->getId()} has no query";
                $payload->getLogger()->debug($message);
                throw new DashboardCreationException($message);
            }

            $queryService->setQueryParamValues($queryNew, $payload->getRedashUser(), $payload->getRestrictions());
            $queryService->enrichQuery($queryNew, $payload->getRestrictions());
            $dataSource = $this->getUserDataSourceForQuery($queryNew, $payload->getRedashUser());
            $queryNew->setDataSource($dataSource);
            $queryProdRedashId = $payload->getRedashWebService()->createQuery($queryNew);
            $queryNew->setProdRedashId($queryProdRedashId);
            $entityManager->persist($queryNew);

            $visualizationNew = clone $visualizationOrigin;
            $visualizationNew
                ->setQuery($queryNew)
                ->setProdRedashId($payload->getRedashWebService()->createVisualization($visualizationNew));
            $entityManager->persist($visualizationNew);

            $widgetNew = clone $widgetOrigin;
            $widgetNew
                ->removeDashboard($dashboard)
                ->setVisualization($visualizationNew);
            $payload->getRedashWebService()->addWidget($widgetNew, $dashboard);
            $payload->getPublishedBoard()->addWidget($widgetNew);
            $entityManager->persist($widgetNew);
        }
        $entityManager->flush();

        return $payload;
    }

    /**
     * @param Query $query
     * @param RedashUser $redashUser
     * @return DataSource
     */
    private function getUserDataSourceForQuery(Query $query, RedashUser $redashUser): DataSource
    {
        foreach ($redashUser->getDataSources() as $dataSource) {
            if ($dataSource->getTestRedashId() === $query->getDataSource()->getTestRedashId()) {
                return $dataSource;
            }
        }

        throw new QueryCreationException(
            'User doesnt have needed DataSource ID: ' . $query->getDataSource()->getTestRedashId()
        );
    }
}