<?php

declare(strict_types=1);

namespace App\DashboardCreationSteps;

use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;

/**
 * Class UpdateUserGroup
 * @package App\DashboardCreationSteps
 */
class UpdateUserGroup
{
    /**
     * @param Payload $payload
     * @return Payload
     * @throws ExceptionInterface
     */
    public function __invoke(Payload $payload)
    {
        $payload->getRedashWebService()
            ->addDataSourceToGroup(
                $payload->getRedashUser(),
                $payload->getUserGroupID()
            );

        return $payload;
    }
}