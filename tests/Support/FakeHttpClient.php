<?php

declare(strict_types=1);

namespace Xtream\Tests\Support;

use RuntimeException;
use Xtream\Http\HttpClientInterface;
use Xtream\Http\Response;

final class FakeHttpClient implements HttpClientInterface
{
    /** @var Response[] */
    private $responses = [];

    /** @var array<int, array{url: string, headers: string[]}> */
    private $requests = [];

    /**
     * @param mixed $payload
     */
    public function queueJson($payload, int $statusCode = 200)
    {
        $body = json_encode($payload);

        if ($body === false) {
            throw new RuntimeException('The fake response could not be encoded.');
        }

        $this->responses[] = new Response($statusCode, $body);
    }

    public function queueResponse(Response $response)
    {
        $this->responses[] = $response;
    }

    public function get(string $url, array $headers = []): Response
    {
        $this->requests[] = ['url' => $url, 'headers' => $headers];

        if ($this->responses === []) {
            throw new RuntimeException('No fake HTTP response is queued.');
        }

        return array_shift($this->responses);
    }

    /**
     * @return array<int, array{url: string, headers: string[]}>
     */
    public function getRequests(): array
    {
        return $this->requests;
    }
}
