<?php

declare(strict_types=1);

namespace Xtream\Tests\Unit;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Xtream\Client;
use Xtream\Serializer\CallbackSerializer;
use Xtream\Serializer\CamelCaseSerializer;
use Xtream\Serializer\JsonApiSerializer;
use Xtream\Serializer\StandardizedSerializer;
use Xtream\Tests\Support\FakeHttpClient;

final class SerializerTest extends TestCase
{
    public function testCamelCaseSerializerMatchesShallowAndDeepResources()
    {
        $serializer = new CamelCaseSerializer();

        $categories = $serializer->serialize('channelCategories', [[
            'category_id' => '10',
            'category_name' => 'News',
        ]]);
        $movie = $serializer->serialize('movie', [
            'movie_data' => ['stream_id' => 20],
        ]);

        self::assertSame('10', $categories[0]['categoryId']);
        self::assertSame('News', $categories[0]['categoryName']);
        self::assertSame(20, $movie['movieData']['streamId']);
    }

    public function testStandardizedSerializerNormalizesProfileAndListings()
    {
        $serializer = new StandardizedSerializer();
        $profile = $serializer->serialize('profile', [
            'auth' => 1,
            'username' => 'customer',
            'is_trial' => '1',
            'active_cons' => '2',
            'max_connections' => '4',
            'created_at' => '100',
            'exp_date' => '200',
        ]);
        $channels = $serializer->serialize('channels', [[
            'stream_id' => 15,
            'name' => 'Channel',
            'num' => 3,
            'tv_archive' => 1,
            'tv_archive_duration' => 7,
            'stream_icon' => 'https://example.com/logo.png',
            'epg_channel_id' => 'epg-15',
            'added' => '300',
            'category_ids' => [4, 5],
            'url' => 'https://example.com/live',
        ]]);

        self::assertSame('customer', $profile['id']);
        self::assertTrue($profile['isTrial']);
        self::assertSame(2, $profile['activeConnections']);
        self::assertSame(4, $profile['maxConnections']);
        self::assertInstanceOf(DateTimeImmutable::class, $profile['expiresAt']);
        self::assertArrayNotHasKey('auth', $profile);
        self::assertSame('15', $channels[0]['id']);
        self::assertTrue($channels[0]['tvArchive']);
        self::assertSame(['4', '5'], $channels[0]['categoryIds']);
    }

    public function testStandardizedSerializerNormalizesShowsAndEpg()
    {
        $serializer = new StandardizedSerializer();
        $show = $serializer->serialize('show', [
            'info' => [
                'series_id' => 30,
                'title' => 'Example Show',
                'rating' => '8.5',
                'category_ids' => [9],
                'backdrop_path' => ['cover.jpg'],
                'episode_run_time' => '45',
                'last_modified' => '400',
            ],
            'seasons' => [],
            'episodes' => [
                '1' => [[
                    'id' => '31',
                    'season' => 1,
                    'episode_num' => '2',
                    'title' => 'Episode',
                    'added' => '500',
                    'info' => [
                        'plot' => 'Plot',
                        'rating' => '9',
                        'release_date' => '2026-01-02',
                        'movie_image' => 'episode.jpg',
                    ],
                ]],
            ],
        ]);
        $epg = $serializer->serialize('fullEPG', [
            'epg_listings' => [[
                'id' => '1',
                'epg_id' => 'epg-1',
                'channel_id' => '15',
                'start' => '2026-01-02 10:00:00',
                'end' => '2026-01-02 11:00:00',
                'title' => base64_encode('News'),
                'description' => base64_encode('Headlines'),
                'lang' => 'en',
                'now_playing' => 1,
                'has_archive' => 0,
            ]],
        ]);

        self::assertSame('30', $show['id']);
        self::assertSame('1', $show['seasons'][0]['id']);
        self::assertSame('31', $show['seasons'][0]['episodes'][0]['id']);
        self::assertSame('News', $epg[0]['title']);
        self::assertSame('Headlines', $epg[0]['description']);
        self::assertTrue($epg[0]['nowPlaying']);
        self::assertFalse($epg[0]['hasArchive']);
    }

    public function testJsonApiSerializerBuildsRelationshipsAndIncludedResources()
    {
        $serializer = new JsonApiSerializer();
        $categories = $serializer->serialize('channelCategories', [
            ['category_id' => '1', 'category_name' => 'Parent', 'parent_id' => '0'],
            ['category_id' => '2', 'category_name' => 'Child', 'parent_id' => '1'],
        ]);
        $channels = $serializer->serialize('channels', [[
            'stream_id' => 15,
            'name' => 'Channel',
            'category_ids' => [2],
        ]]);

        self::assertSame('channel-category', $categories['data'][0]['type']);
        self::assertArrayNotHasKey('relationships', $categories['data'][0]);
        self::assertSame(
            ['type' => 'channel-category', 'id' => '1'],
            $categories['data'][1]['relationships']['parent']['data']
        );
        self::assertSame('channel', $channels['data'][0]['type']);
        self::assertSame(
            ['type' => 'channel-category', 'id' => '2'],
            $channels['data'][0]['relationships']['categories']['data'][0]
        );
    }

    public function testClientUsesPartialCustomSerializerCallbacks()
    {
        $http = new FakeHttpClient();
        $http->queueJson([['category_id' => '1', 'category_name' => 'Live']]);
        $serializer = new CallbackSerializer('Custom', [
            'channelCategories' => static function (array $categories): array {
                return [['label' => $categories[0]['category_name']]];
            },
        ]);
        $client = new Client([
            'url' => 'https://example.com',
            'username' => 'user',
            'password' => 'secret',
            'http_client' => $http,
            'serializer' => $serializer,
        ]);

        self::assertSame('Custom', $client->getSerializerType());
        self::assertSame([['label' => 'Live']], $client->getChannelCategories());
    }
}
