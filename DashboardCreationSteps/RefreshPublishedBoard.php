<?php

declare(strict_types=1);

namespace App\DashboardCreationSteps;

use App\Entity\Dashboard;
use App\Entity\PublishedDashboard;
use App\Exception\DashboardCreationException;
use App\Service\QueryService;
use App\Service\RedashProvider\RedashTargetWebService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;

/**
 * Class RefreshPublishedBoard
 * @package App\DashboardCreationSteps
 */
class RefreshPublishedBoard
{
    /**
     * @param Payload $payload
     * @return Payload
     * @throws ExceptionInterface
     */
    public function __invoke(Payload $payload)
    {
        $em = $payload->getEntityManager();
        $dashboardRepository = $em->getRepository(Dashboard::class);

        $dashboard = $dashboardRepository->find($payload->getDashboardId());
        if ($dashboard === null) {
            $payload->getLogger()->debug("Dashboard with id {$payload->getDashboardId()} not found");
            throw new DashboardCreationException("Dashboard with id {$payload->getDashboardId()} not found");
        }

        if ($dashboard->getIsArchived()) {
            $payload->getLogger()->debug("Dashboard with id {$payload->getDashboardId()} archived");
            throw new DashboardCreationException("Dashboard with id {$payload->getDashboardId()} archived");
        }

        foreach ($dashboard->getPublishedDashboards() as $publishedDashboard) {
            if ( !empty($publishedDashboard->getUrl())
                && $publishedDashboard->getRedashUser()->getId() === $payload->getRedashUser()->getId()
                && $publishedDashboard->isRestrictionsEqual($payload->getRestrictions()->toArray())
            ) {
                $payload->getLogger()->debug("Dashboard {$dashboard->getId()} already published");
                $queryService = $payload->getServiceContainer()->get(QueryService::class);
                $this->refreshQueries($publishedDashboard, $payload->getRedashWebService(), $em, $queryService);
                $payload->setPublishedBoard($publishedDashboard);
                $payload->setOkay(false);

                return $payload;
            }
        }
        $payload->setDashboard($dashboard);

        return $payload;
    }

    /**
     * @param PublishedDashboard $publishedBoard
     * @param RedashTargetWebService $redashWS
     * @param EntityManagerInterface $em
     * @param QueryService $queryService
     * @throws ExceptionInterface
     */
    public function refreshQueries(
        PublishedDashboard $publishedBoard,
        RedashTargetWebService $redashWS,
        EntityManagerInterface $em,
        QueryService $queryService
    ): void
    {
        foreach ($publishedBoard->getWidgets() as $widget) {
            $query = $widget->getVisualization()->getQuery();
            $options = $query->getOptions();
            $needUpdateQuery = false;
            foreach ($options['parameters'] as $key => $parameter) {
                if (isset($parameter['queryId'])) {
                    $queryResult = $redashWS->runQueryAndWaitResult($parameter['queryId']);
                    $updatedValue = $queryService->updateParamFromQueryResult($parameter['value'], $queryResult);
                    if ($parameter['value'] !== $updatedValue) {
                        $parameter['value'] = $updatedValue;
                        $options['parameters'][$key] = $parameter;
                        $needUpdateQuery = true;
                    }
                }
            }
            if ($needUpdateQuery) {
                $query->setOptions($options);
                $em->persist($query);
                $redashWS->updateQuery($query);
            }
            $redashWS->runQuery($query->getProdRedashId(), $query->getParameters());
        }
        $em->flush();
    }
}