<?php

declare(strict_types=1);


namespace App\Service\Dashboards;

/**
 * Class DashboardUrlProcessor
 * @package App\Service\Dashboards
 */
final class DashboardUrlProcessor
{
    /**
     * @var string
     */
    private string $urlScheme;

    /**
     * DashboardUrlProcessor constructor.
     * @param string $urlScheme
     */
    public function __construct(string $urlScheme)
    {
        $this->urlScheme = $urlScheme;
    }

    /**
     * @param string $url
     * @return string
     */
    public function process(string $url): string
    {
        $dashboardUrl = parse_url($url);
        $dashboardUrl['scheme'] = $this->urlScheme;

        return $this->unparse_url($dashboardUrl);
    }

    /**
     * @param array $parsed_url
     * @return string
     */
    private function unparse_url(array $parsed_url): string
    {
        $scheme   = isset($parsed_url['scheme']) ? $parsed_url['scheme'] . '://' : '';
        $host     = isset($parsed_url['host']) ? $parsed_url['host'] : '';
        $port     = isset($parsed_url['port']) ? ':' . $parsed_url['port'] : '';
        $user     = isset($parsed_url['user']) ? $parsed_url['user'] : '';
        $pass     = isset($parsed_url['pass']) ? ':' . $parsed_url['pass']  : '';
        $pass     = ($user || $pass) ? "$pass@" : '';
        $path     = isset($parsed_url['path']) ? $parsed_url['path'] : '';
        $query    = isset($parsed_url['query']) ? '?' . $parsed_url['query'] : '';
        $fragment = isset($parsed_url['fragment']) ? '#' . $parsed_url['fragment'] : '';

        return "$scheme$user$pass$host$port$path$query$fragment";
    }

}