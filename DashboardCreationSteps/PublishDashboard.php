<?php

declare(strict_types=1);

namespace App\DashboardCreationSteps;


use App\Entity\PublishedDashboard;
use App\Service\Dashboards\DashboardUrlProcessor;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;

class PublishDashboard
{
    /**
     * @param Payload $payload
     * @return Payload
     * @throws ExceptionInterface
     */
    public function __invoke(Payload $payload)
    {
        $dashboard = $payload->getDashboard();
        $entityManager = $payload->getEntityManager();
        $dashboardUrl = $payload->getRedashWebService()->publishDashboard($dashboard);
        /** @var DashboardUrlProcessor $urlProcessor */
        $urlProcessor = $payload->getServiceContainer()->get(DashboardUrlProcessor::class);
        $dashboardUrl = $urlProcessor->process($dashboardUrl);
        $publishedDashboard = $payload->getPublishedBoard();
        $entityManager->persist($publishedDashboard->setUrl($dashboardUrl));
        $entityManager->persist($dashboard);
        $entityManager->flush();

        return $payload;
    }
}