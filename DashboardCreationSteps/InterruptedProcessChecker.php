<?php

declare(strict_types=1);

namespace App\DashboardCreationSteps;

use App\Entity\PublishedDashboard;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;

/**
 * Class InterruptedProcessChecker
 * @package App\DashboardCreationSteps
 */
class InterruptedProcessChecker
{
    /**
     * @param Payload $payload
     * @return bool
     */
    public function __invoke(Payload $payload): bool
    {
        if (false === $payload->isOkay()) {
            $payload->getLogger()->debug('Dashboard creation pipeline interrupted');
        }

        return $payload->isOkay();
    }
}