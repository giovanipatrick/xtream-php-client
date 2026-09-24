<?php

declare(strict_types=1);

namespace Xtream\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Xtream\Client;

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
}
