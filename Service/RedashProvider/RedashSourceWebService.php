<?php

declare(strict_types=1);

namespace App\Service\RedashProvider;

use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;

/**
 * Class RedashSourceWebService
 * @package App\Service\RedashProvider
 */
final class RedashSourceWebService
{

    public const REQUEST_DASHBOARDS_PER_PAGE = 250;

    /**
     * @var SourceClient
     */
    protected SourceClient $client;

    public function __construct(SourceClient $client)
    {
        $this->client = $client;
    }

    /**
     * @return array
     * @throws ExceptionInterface
     */
    public function getAllDashboards(): array
    {
        $request = $this->client->createRequest('GET', 'dashboards');
        $request->setQueryParameter(['page_size' => self::REQUEST_DASHBOARDS_PER_PAGE]);
        $response = $this->client->send($request);
        $result = $response->toArray();

        return $result['results'] ?? [];
    }

    /**
     * @param string $slug
     * @return array|null
     * @throws ExceptionInterface
     */
    public function getDashboardDataBySlug(string $slug): ?array
    {
        $request = $this->client->createRequest('GET', 'dashboards/' . $slug);
        return $this
            ->client
            ->send($request)
            ->toArray();
    }

    /**
     * @return array|null
     * @throws ExceptionInterface
     */
    public function getDataSources(): ?array
    {
        $request = $this->client->createRequest('GET', 'data_sources');

        return $this->client->send($request)->toArray();
    }

    /**
     * @param int $redashQueryId
     * @return array
     * @throws ExceptionInterface
     */
    public function getQuery(int $redashQueryId): array
    {
        $request = $this->client->createRequest('GET', 'queries/' . $redashQueryId);

        return $this->client->send($request)->toArray();
    }
}
