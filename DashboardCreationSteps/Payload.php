<?php

declare(strict_types=1);

namespace App\DashboardCreationSteps;

use App\DashboardLink\DashboardRestrictionsGroups;
use App\Entity\Dashboard;
use App\Entity\PublishedDashboard;
use App\Entity\RedashUser;
use App\Security\User;
use App\Service\RedashProvider\RedashTargetWebService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Class Payload
 * @package App\DashboardCreationSteps
 */
class Payload
{
    /**
     * @var RedashUser
     */
    private RedashUser $redashUser;
    /**
     * @var Dashboard
     */
    private Dashboard $dashboard;
    /**
     * @var PublishedDashboard
     */
    private PublishedDashboard $publishedBoard;
    /**
     * @var int
     */
    private int $dashboardId;
    /**
     * @var User
     */
    private User $user;
    /**
     * @var int
     */
    private int $userGroupID;
    /**
     * @var DashboardRestrictionsGroups
     */
    private DashboardRestrictionsGroups $restrictions;
    /**
     * @var RedashTargetWebService|null
     */
    private ?RedashTargetWebService $redashWebService;
    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $entityManager;
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;
    /**
     * @var bool
     */
    private bool $okay = true;
    /**
     * @var ContainerInterface
     */
    private ContainerInterface $serviceContainer;
    /**
     * @var ParameterBagInterface
     */
    private ParameterBagInterface $parameterBag;

    /**
     * Payload constructor.
     * @param EntityManagerInterface $entityManager
     * @param LoggerInterface $logger
     * @param ContainerInterface $serviceContainer
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        LoggerInterface $logger,
        ContainerInterface $serviceContainer,
        ParameterBagInterface $parameterBag
    ) {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->serviceContainer = $serviceContainer;
        $this->parameterBag = $parameterBag;
    }

    /**
     * @return PublishedDashboard
     */
    public function getPublishedBoard(): PublishedDashboard
    {
        return $this->publishedBoard;
    }

    /**
     * @param PublishedDashboard $publishedBoard
     */
    public function setPublishedBoard(PublishedDashboard $publishedBoard): void
    {
        $this->publishedBoard = $publishedBoard;
    }

    /**
     * @return ContainerInterface
     */
    public function getServiceContainer(): ContainerInterface
    {
        return $this->serviceContainer;
    }

    /**
     * @return bool
     */
    public function isOkay(): bool
    {
        return $this->okay;
    }

    /**
     * @param bool $okay
     */
    public function setOkay(bool $okay): void
    {
        $this->okay = $okay;
    }

    /**
     * @return RedashTargetWebService
     */
    public function getRedashWebService(): RedashTargetWebService
    {
        if (empty($this->redashWebService)) {
            $this->redashWebService = $this->serviceContainer->get(RedashTargetWebService::class);
        }

        return $this->redashWebService;
    }

    /**
     * @return EntityManagerInterface
     */
    public function getEntityManager(): EntityManagerInterface
    {
        return $this->entityManager;
    }

    /**
     * @return LoggerInterface
     */
    public function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * @return ParameterBagInterface
     */
    public function getContainer(): ParameterBagInterface
    {
        return $this->parameterBag;
    }

    /**
     * @param int $dashboardId
     * @return $this
     */
    public function setDashboardId(int $dashboardId): Payload
    {
        $this->dashboardId = $dashboardId;

        return $this;
    }

    /**
     * @return int
     */
    public function getDashboardId(): int
    {
        return $this->dashboardId;
    }

    /**
     * @param User $user
     * @return $this
     */
    public function setUser(User $user): Payload
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param DashboardRestrictionsGroups $restrictions
     * @return $this
     */
    public function setRestrictions(DashboardRestrictionsGroups $restrictions): Payload
    {
        $this->restrictions = $restrictions;

        return $this;
    }

    /**
     * @return DashboardRestrictionsGroups
     */
    public function getRestrictions(): DashboardRestrictionsGroups
    {
        return $this->restrictions;
    }

    /**
     * @return int
     */
    public function getUserGroupID(): int
    {
        return $this->userGroupID;
    }

    /**
     * @param Dashboard $dashboard
     */
    public function setDashboard(Dashboard $dashboard): void
    {
        $this->dashboard = $dashboard;
    }

    /**
     * @return RedashUser
     */
    public function getRedashUser(): RedashUser
    {
        return $this->redashUser;
    }

    /**
     * @param RedashUser $redashUser
     */
    public function setRedashUser(RedashUser $redashUser): void
    {
        $this->redashUser = $redashUser;
    }

    /**
     * @param int $userGroupID
     */
    public function setUserGroupID(int $userGroupID): void
    {
        $this->userGroupID = $userGroupID;
    }

    /**
     * @return Dashboard
     */
    public function getDashboard(): Dashboard
    {
        return $this->dashboard;
    }
}