<?php

declare(strict_types=1);

namespace App\Service\RedashProvider;

/**
 * Class Request
 * @package App\Service\RedashProvider
 */
final class Request
{

    /**
     * @var string
     */
    private string $requestMethod;

    /**
     * @var string
     */
    private string $path;

    /**
     * @var array
     */
    private ?array $body = [];

    /**
     * @var array
     */
    private ?array $queryParam = null;

    /**
     * Request constructor.
     * @param string $requestMethod
     * @param string $path
     */
    public function __construct(string $requestMethod, string $path)
    {
        $this->requestMethod = $requestMethod;
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function getRequestMethod(): string
    {
        return $this->requestMethod;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return empty($this->queryParam) ? $this->path : $this->path . '?' . http_build_query($this->queryParam);
    }

    /**
     * @param array $body
     * @return Request
     */
    public function setBody(array $body = []): self
    {
        $this->body = $body;

        return $this;
    }

    /**
     * @param array $queryParam
     * @return Request
     */
    public function setQueryParameter(array $queryParam): self
    {
        $this->queryParam = $queryParam;

        return $this;
    }

    /**
     * @return array
     */
    public function getBody(): array
    {
        return $this->body;
    }
}