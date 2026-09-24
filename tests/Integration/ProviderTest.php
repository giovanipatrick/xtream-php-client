<?php

declare(strict_types=1);

namespace Xtream\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Xtream\Client;
use Xtream\Serializer\CamelCaseSerializer;
use Xtream\Serializer\JsonApiSerializer;
use Xtream\Serializer\StandardizedSerializer;

final class ProviderTest extends TestCase
{
    public function testProviderProfileAndCategories()
    {
        $baseUrl = getenv('XTREAM_BASE_URL');
        $username = getenv('XTREAM_USERNAME');
        $password = getenv('XTREAM_PASSWORD');

        if (!$baseUrl || !$username || !$password) {
            self::markTestSkipped('Live provider environment variables are not configured.');
        }

        $client = new Client([
            'url' => $baseUrl,
            'username' => $username,
            'password' => $password,
        ]);

        self::assertSame('array', gettype($client->getProfile()));
        self::assertSame('array', gettype($client->getServerInfo()));
        self::assertSame('array', gettype($client->getChannelCategories()));
        self::assertSame('array', gettype($client->getMovieCategories()));
        self::assertSame('array', gettype($client->getShowCategories()));
    }

    public function testProviderProfilesCanBeSerialized()
    {
        $baseUrl = getenv('XTREAM_BASE_URL');
        $username = getenv('XTREAM_USERNAME');
        $password = getenv('XTREAM_PASSWORD');

        if (!$baseUrl || !$username || !$password) {
            self::markTestSkipped('Live provider environment variables are not configured.');
        }

        $camelClient = new Client([
            'url' => $baseUrl,
            'username' => $username,
            'password' => $password,
            'serializer' => new CamelCaseSerializer(),
        ]);
        $standardClient = new Client([
            'url' => $baseUrl,
            'username' => $username,
            'password' => $password,
            'serializer' => new StandardizedSerializer(),
        ]);
        $jsonApiClient = new Client([
            'url' => $baseUrl,
            'username' => $username,
            'password' => $password,
            'serializer' => new JsonApiSerializer(),
        ]);

        self::assertArrayHasKey('allowedOutputFormats', $camelClient->getProfile());
        self::assertArrayHasKey('id', $standardClient->getProfile());
        self::assertSame('user-profile', $jsonApiClient->getProfile()['data']['type']);
    }
}
