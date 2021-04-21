<?php

declare(strict_types=1);

namespace App\DashboardCreationSteps;

use App\Entity\RedashUser;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;

/**
 * Class CreateUser
 * @package App\DashboardCreationSteps
 */
class CreateUser
{
    /**
     * @param Payload $payload
     * @return Payload
     * @throws ExceptionInterface
     */
    public function __invoke(Payload $payload)
    {
        $payload->getLogger()->debug("Create new user {$payload->getUser()->getEmail()}");
        $redashUser = new RedashUser();

        $userData = $payload->getRedashWebService()->createUser($payload->getUser(), $payload->getUserGroupID());
        $redashUser->fromArray($userData);
        $redashUser->setPassword($payload->getUser()->getPassword());
        $payload->getEntityManager()->persist($redashUser);
        $payload->getEntityManager()->flush();
        $payload->setRedashUser($redashUser);

        return $payload;
    }
}