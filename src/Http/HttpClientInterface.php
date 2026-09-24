<?php

declare(strict_types=1);

namespace Xtream\Http;

interface HttpClientInterface
{
    /**
     * @param string[] $headers
     */
    public function get(string $url, array $headers = []): Response;
}
