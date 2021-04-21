<?php

declare(strict_types=1);

namespace App\Service\RedashProvider;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * Class SourceClient
 * @package App\Service\RedashProvider
 */
final class SourceClient
{

    /**
     * @var HttpClientInterface
     */
    private HttpClientInterface $httpClient;
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * SourceClient constructor.
     * @param string $baseUrl
     * @param string $apiKey
     * @param LoggerInterface $logger
     */
    public function __construct(string $baseUrl, string $apiKey, LoggerInterface $logger)
    {
        $this->httpClient = HttpClient::createForBaseUri($baseUrl, ['headers' => ['Authorization' => $apiKey,]]);
        $this->logger = $logger;
    }

    /**
     * @param Request $request
     * @return ResponseInterface
     */
    public function send(Request $request): ResponseInterface
    {
        try {
            $response = $this->httpClient->request($request->getRequestMethod(), $request->getPath());
        } catch (TransportExceptionInterface $e) {
            $this->logger->error($e->getMessage());
        }

        return $response;
    }

    /**
     * @param string $requestMethod
     * @param string $path
     * @return Request
     */
    public function createRequest(string $requestMethod, string $path): Request
    {
        return new Request($requestMethod, $path);
    }
}