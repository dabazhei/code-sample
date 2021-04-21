<?php

declare(strict_types=1);

namespace App\DashboardCreationSteps;

use App\Entity\Dashboard;
use App\Entity\PublishedDashboard;
use App\Exception\DashboardCreationException;
use App\Service\RedashProvider\RedashTargetWebService;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;

/**
 * Class CreateDashboard
 * @package App\DashboardCreationSteps
 */
class CreateDashboard
{
    /**
     * @param Payload $payload
     * @return Payload
     * @throws ExceptionInterface
     */
    public function __invoke(Payload $payload)
    {
        $em = $payload->getEntityManager();
        $dashboard = $payload->getDashboard();

        $dashboardData = $payload->getRedashWebService()->createDashboard($dashboard);
        $dashboard->setProdRedashId($dashboardData['id']);
        $publishedBoard = (new PublishedDashboard())
            ->setProdRedashId($dashboardData['id'])
            ->setSlug($dashboardData['slug'])
            ->setDashboard($dashboard)
            ->setRedashUser($payload->getRedashUser())
            ->setAssortment($payload->getRestrictions()->getAssortmentAsArray())
            ->setLocation($payload->getRestrictions()->getLocationAsArray())
        ;
        $payload->setPublishedBoard($publishedBoard);
        $payload->setDashboard($dashboard);
        $em->persist($publishedBoard);
        $em->persist($dashboard->addPublishedDashboard($publishedBoard));
        $em->flush();

        return $payload;
    }
}