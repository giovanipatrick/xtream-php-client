<?php

declare(strict_types=1);

namespace Xtream\Tests\Unit;

use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\TestCase;
use Xtream\Client;

final class ClientUrlTest extends TestCase
{
    public function testItGeneratesChannelMovieAndEpisodeUrls()
    {
        $client = $this->client();

        self::assertSame(
            'https://example.com/live/user/name/10.m3u8',
            $client->generateStreamUrl(['type' => 'channel', 'stream_id' => 10, 'extension' => 'm3u8'])
        );
        self::assertSame(
            'https://example.com/movie/user/name/20.mkv',
            $client->generateStreamUrl(['type' => 'movie', 'stream_id' => 20, 'extension' => 'mkv'])
        );
        self::assertSame(
            'https://example.com/series/user/name/30.mp4',
            $client->generateStreamUrl(['type' => 'episode', 'stream_id' => 30, 'extension' => 'mp4'])
        );
    }

    public function testItGeneratesUtcTimeshiftUrls()
    {
        $client = $this->client();
        $start = new DateTimeImmutable('2026-09-24 18:30:00', new DateTimeZone('America/Sao_Paulo'));

        self::assertSame(
            'https://example.com/timeshift/user/name/60/2026-09-24:21-30/10.ts',
            $client->generateStreamUrl([
                'type' => 'channel',
                'stream_id' => 10,
                'timeshift' => ['start' => $start, 'duration' => 60],
            ])
        );
    }

    public function testItGeneratesAnXmltvUrl()
    {
        self::assertSame(
            'https://example.com/xmltv.php?username=user&password=name',
            $this->client()->getXmltvUrl()
        );
    }

    private function client(): Client
    {
        return new Client([
            'url' => 'https://example.com',
            'username' => 'user',
            'password' => 'name',
            'preferred_format' => 'm3u8',
        ]);
    }
}
