<?php

declare(strict_types=1);

namespace Xtream\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Xtream\Client;
use Xtream\Exception\AuthenticationException;
use Xtream\Exception\HttpException;
use Xtream\Exception\InvalidResponseException;
use Xtream\Exception\NotFoundException;
use Xtream\Http\Response;
use Xtream\Tests\Support\FakeHttpClient;

final class ClientErrorTest extends TestCase
{
    public function testItRejectsInvalidJsonWithoutExposingTheResponse()
    {
        $http = new FakeHttpClient();
        $http->queueResponse(new Response(200, 'sensitive invalid response'));

        try {
            $this->client($http)->getChannelCategories();
            self::fail('Expected invalid JSON to be rejected.');
        } catch (InvalidResponseException $exception) {
            self::assertFalse(strpos($exception->getMessage(), 'sensitive'));
        }
    }

    public function testItReportsHttpStatusWithoutExposingTheRequestUrl()
    {
        $http = new FakeHttpClient();
        $http->queueResponse(new Response(503, 'unavailable'));

        try {
            $this->client($http)->getChannelCategories();
            self::fail('Expected the HTTP error to be reported.');
        } catch (HttpException $exception) {
            self::assertSame(503, $exception->getStatusCode());
            self::assertFalse(strpos($exception->getMessage(), 'password'));
        }
    }

    public function testItRejectsInvalidCredentials()
    {
        $http = new FakeHttpClient();
        $http->queueJson(['user_info' => ['auth' => 0]]);

        $this->expectException(AuthenticationException::class);
        $this->client($http)->getProfile();
    }

    public function testItReportsMissingMovies()
    {
        $http = new FakeHttpClient();
        $http->queueJson($this->profilePayload());
        $http->queueJson(['info' => [], 'movie_data' => []]);

        $this->expectException(NotFoundException::class);
        $this->client($http)->getMovie(['movie_id' => 404]);
    }

    private function client(FakeHttpClient $http): Client
    {
        return new Client([
            'url' => 'https://example.com',
            'username' => 'user',
            'password' => 'password',
            'http_client' => $http,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function profilePayload(): array
    {
        return [
            'user_info' => ['auth' => 1, 'allowed_output_formats' => ['m3u8']],
            'server_info' => [],
        ];
    }
}
