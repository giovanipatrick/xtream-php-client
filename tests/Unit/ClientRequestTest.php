<?php

declare(strict_types=1);

namespace Xtream\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Xtream\Client;
use Xtream\Tests\Support\FakeHttpClient;

final class ClientRequestTest extends TestCase
{
    public function testItCachesProfileAndServerInformation()
    {
        $http = new FakeHttpClient();
        $http->queueJson($this->profilePayload());
        $client = $this->client($http);

        self::assertSame('Active', $client->getProfile()['status']);
        self::assertSame('UTC', $client->getServerInfo()['timezone']);
        self::assertCount(1, $http->getRequests());

        $query = [];
        parse_str((string) parse_url($http->getRequests()[0]['url'], PHP_URL_QUERY), $query);
        self::assertSame('user+name', $query['username']);
        self::assertSame('p@ss word', $query['password']);
        self::assertArrayNotHasKey('action', $query);
        self::assertSame(['Accept: application/json'], $http->getRequests()[0]['headers']);
    }

    public function testItRetrievesAllCategoryTypes()
    {
        $http = new FakeHttpClient();
        $http->queueJson([['category_id' => '1', 'category_name' => 'Live']]);
        $http->queueJson([['category_id' => '2', 'category_name' => 'Movies']]);
        $http->queueJson([['category_id' => '3', 'category_name' => 'Shows']]);
        $client = $this->client($http);

        self::assertSame('Live', $client->getChannelCategories()[0]['category_name']);
        self::assertSame('Movies', $client->getMovieCategories()[0]['category_name']);
        self::assertSame('Shows', $client->getShowCategories()[0]['category_name']);
        self::assertSame(
            ['get_live_categories', 'get_vod_categories', 'get_series_categories'],
            $this->actions($http)
        );
    }

    public function testItFiltersPaginatesAndAddsChannelUrls()
    {
        $http = new FakeHttpClient();
        $http->queueJson($this->profilePayload(['ts']));
        $http->queueJson([
            ['stream_id' => 10, 'name' => 'One'],
            ['stream_id' => 11, 'name' => 'Two'],
            ['stream_id' => 12, 'name' => 'Three'],
        ]);
        $client = $this->client($http);
        $channels = $client->getChannels([
            'category_id' => 7,
            'page' => 2,
            'limit' => 1,
        ]);

        self::assertCount(1, $channels);
        self::assertSame('Two', $channels[0]['name']);
        self::assertSame(
            'https://example.com/live/user%2Bname/p%40ss%20word/11.ts',
            $channels[0]['url']
        );

        $query = [];
        parse_str((string) parse_url($http->getRequests()[1]['url'], PHP_URL_QUERY), $query);
        self::assertSame('get_live_streams', $query['action']);
        self::assertSame('7', $query['category_id']);
    }

    public function testItAddsUrlsToMovieListingsAndDetails()
    {
        $http = new FakeHttpClient();
        $http->queueJson($this->profilePayload());
        $http->queueJson([
            ['stream_id' => 20, 'container_extension' => 'mkv', 'name' => 'Movie'],
        ]);
        $http->queueJson([
            'info' => ['name' => 'Movie'],
            'movie_data' => ['stream_id' => 20, 'container_extension' => 'mkv'],
        ]);
        $client = $this->client($http);

        $movies = $client->getMovies();
        $movie = $client->getMovie(['movieId' => 20]);

        self::assertSame(
            'https://example.com/movie/user%2Bname/p%40ss%20word/20.mkv',
            $movies[0]['url']
        );
        self::assertSame($movies[0]['url'], $movie['url']);
        self::assertSame(['get_vod_streams', 'get_vod_info'], array_slice($this->actions($http), 1));
    }

    public function testItAddsUrlsToShowEpisodes()
    {
        $http = new FakeHttpClient();
        $http->queueJson($this->profilePayload());
        $http->queueJson([
            'info' => ['name' => 'Show'],
            'episodes' => [
                '1' => [
                    ['id' => '31', 'container_extension' => 'mp4'],
                ],
            ],
        ]);
        $client = $this->client($http);
        $show = $client->getShow(['show_id' => 30]);

        self::assertSame(30, $show['info']['series_id']);
        self::assertSame(
            'https://example.com/series/user%2Bname/p%40ss%20word/31.mp4',
            $show['episodes']['1'][0]['url']
        );
    }

    public function testItRetrievesShowsAndEpgData()
    {
        $http = new FakeHttpClient();
        $http->queueJson([['series_id' => 30, 'name' => 'Show']]);
        $http->queueJson(['epg_listings' => [['id' => '1']]]);
        $http->queueJson(['epg_listings' => [['id' => '2']]]);
        $client = $this->client($http);

        self::assertSame('Show', $client->getShows(['categoryId' => 9])[0]['name']);
        self::assertSame('1', $client->getShortEpg(['channelId' => 10, 'limit' => 2])['epg_listings'][0]['id']);
        self::assertSame('2', $client->getFullEpg(['channel_id' => 10])['epg_listings'][0]['id']);

        $requests = $http->getRequests();
        $shortQuery = [];
        parse_str((string) parse_url($requests[1]['url'], PHP_URL_QUERY), $shortQuery);
        self::assertSame('2', $shortQuery['limit']);
    }

    private function client(FakeHttpClient $http): Client
    {
        return new Client([
            'url' => 'https://example.com/',
            'username' => 'user+name',
            'password' => 'p@ss word',
            'preferred_format' => 'm3u8',
            'http_client' => $http,
        ]);
    }

    /**
     * @param string[] $formats
     *
     * @return array<string, mixed>
     */
    private function profilePayload(array $formats = ['m3u8', 'ts']): array
    {
        return [
            'user_info' => [
                'auth' => 1,
                'status' => 'Active',
                'allowed_output_formats' => $formats,
            ],
            'server_info' => ['timezone' => 'UTC'],
        ];
    }

    /**
     * @return string[]
     */
    private function actions(FakeHttpClient $http): array
    {
        $actions = [];

        foreach ($http->getRequests() as $request) {
            $query = [];
            parse_str((string) parse_url($request['url'], PHP_URL_QUERY), $query);
            $actions[] = isset($query['action']) ? $query['action'] : '';
        }

        return $actions;
    }
}
