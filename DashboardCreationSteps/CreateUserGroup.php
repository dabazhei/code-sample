<?php

declare(strict_types=1);

namespace App\DashboardCreationSteps;

use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;

/**
 * Class CreateUserGroup
 * @package App\DashboardCreationSteps
 */
class CreateUserGroup
{
    /**
     * @param Payload $payload
     * @return Payload
     * @throws ExceptionInterface
     */
    public function __invoke(Payload $payload)
    {
        $userGroupID = $payload->getRedashWebService()->createGroup($payload->getUser());
        $payload->setUserGroupID($userGroupID);

        return $payload;
    }
}