<?php

declare(strict_types=1);

namespace App\Service\RedashProvider;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * Class TargetClient
 * @package App\Service\RedashProvider
 */
final class TargetClient
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
     * @return Response
     * @throws ExceptionInterface
     */
    public function send(Request $request): Response
    {
        $this->logger->debug(
            'Request ' . $request->getRequestMethod() . ' ' . $request->getPath() . json_encode($request->getBody())
        );

        try {
            $response = $this->httpClient->request(
                $request->getRequestMethod(),
                $request->getPath(),
                ['json' => $request->getBody()]
            );

            $this->logger->debug('Response ' . $response->getContent());
        } catch (ExceptionInterface $e) {
            $this->logger->error('Response Error ' . $e->getMessage());

            return new NullResponse();
        }

        return (null === $response) ? new NullResponse() : new Response($response);
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