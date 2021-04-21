<?php

declare(strict_types=1);

namespace App\Service\RedashProvider;

use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class Response
{
    /**
     * @var ResponseInterface
     */
    private ResponseInterface $response;
    /**
     * @var array
     */
    private array $responseAsArray;

    /**
     * Response constructor.
     * @param ResponseInterface $response
     * @throws ExceptionInterface
     */
    public function __construct(ResponseInterface $response)
    {
        $this->responseAsArray = $response->toArray(false);
    }

    /**
     * @return array
     */
    public function asArray(): array
    {
        return $this->responseAsArray;
    }

    /**
     * @param string $key
     * @param null $defaultValue
     * @return mixed|null
     */
    public function get(string $key, $defaultValue = null)
    {
        return $this->responseAsArray[$key] ?? $defaultValue;
    }
}