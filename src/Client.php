<?php

declare(strict_types=1);

namespace Xtream;

use DateTimeInterface;
use Xtream\Exception\AuthenticationException;
use Xtream\Exception\HttpException;
use Xtream\Exception\InvalidConfigurationException;
use Xtream\Exception\InvalidResponseException;
use Xtream\Exception\NotFoundException;
use Xtream\Http\CurlHttpClient;
use Xtream\Http\HttpClientInterface;

/**
 * Client for an Xtream-compatible Player API.
 */
final class Client
{
    /** @var string */
    private $baseUrl;

    /** @var string */
    private $username;

    /** @var string */
    private $password;

    /** @var string */
    private $preferredFormat;

    /** @var HttpClientInterface */
    private $httpClient;

    /** @var array<string, mixed>|null */
    private $profilePayload;

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(array $options)
    {
        $this->baseUrl = $this->baseUrl($this->requiredString($options, 'url'));
        $this->username = trim($this->requiredString($options, 'username'));
        $this->password = trim($this->requiredString($options, 'password'));
        $this->preferredFormat = isset($options['preferred_format'])
            ? $this->streamFormat($options['preferred_format'])
            : 'ts';
        $this->profilePayload = null;

        if (isset($options['http_client'])) {
            if (!$options['http_client'] instanceof HttpClientInterface) {
                throw new InvalidConfigurationException(
                    'The "http_client" option must implement HttpClientInterface.'
                );
            }

            $this->httpClient = $options['http_client'];
        } else {
            $this->httpClient = new CurlHttpClient($options);
        }
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function getPreferredFormat(): string
    {
        return $this->preferredFormat;
    }

    /**
     * @return array<string, mixed>
     */
    public function getProfile(): array
    {
        $payload = $this->profilePayload();

        if (!isset($payload['user_info']) || !is_array($payload['user_info'])) {
            throw new InvalidResponseException('The Xtream API response has no user information.');
        }

        return $payload['user_info'];
    }

    /**
     * @return array<string, mixed>
     */
    public function getServerInfo(): array
    {
        $payload = $this->profilePayload();

        if (!isset($payload['server_info']) || !is_array($payload['server_info'])) {
            throw new InvalidResponseException('The Xtream API response has no server information.');
        }

        return $payload['server_info'];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getChannelCategories(): array
    {
        return $this->listRequest('get_live_categories');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getMovieCategories(): array
    {
        return $this->listRequest('get_vod_categories');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getShowCategories(): array
    {
        return $this->listRequest('get_series_categories');
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return array<int, array<string, mixed>>
     */
    public function getChannels(array $options = []): array
    {
        $this->profilePayload();
        $channels = $this->filterableRequest('get_live_streams', $options);

        foreach ($channels as &$channel) {
            if (isset($channel['stream_id'])) {
                $channel['url'] = $this->generateStreamUrl([
                    'type' => 'channel',
                    'stream_id' => $channel['stream_id'],
                    'extension' => $this->preferredFormat,
                ]);
            }
        }
        unset($channel);

        return $channels;
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return array<int, array<string, mixed>>
     */
    public function getMovies(array $options = []): array
    {
        $this->profilePayload();
        $movies = $this->filterableRequest('get_vod_streams', $options);

        foreach ($movies as &$movie) {
            if (isset($movie['stream_id'], $movie['container_extension'])) {
                $movie['url'] = $this->generateStreamUrl([
                    'type' => 'movie',
                    'stream_id' => $movie['stream_id'],
                    'extension' => $movie['container_extension'],
                ]);
            }
        }
        unset($movie);

        return $movies;
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return array<string, mixed>
     */
    public function getMovie(array $options): array
    {
        $movieId = $this->requiredIdentifier($options, 'movie_id', 'movieId');
        $this->profilePayload();
        $movie = $this->request('get_vod_info', ['vod_id' => $movieId]);

        if (!isset($movie['info']) || $movie['info'] === []) {
            throw new NotFoundException('The requested movie was not found.');
        }

        if (!isset($movie['movie_data']) || !is_array($movie['movie_data'])) {
            throw new InvalidResponseException('The movie response has no stream data.');
        }

        $data = $movie['movie_data'];

        if (isset($data['stream_id'], $data['container_extension'])) {
            $movie['url'] = $this->generateStreamUrl([
                'type' => 'movie',
                'stream_id' => $data['stream_id'],
                'extension' => $data['container_extension'],
            ]);
        }

        return $movie;
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return array<int, array<string, mixed>>
     */
    public function getShows(array $options = []): array
    {
        return $this->filterableRequest('get_series', $options);
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return array<string, mixed>
     */
    public function getShow(array $options): array
    {
        $showId = $this->requiredIdentifier($options, 'show_id', 'showId');
        $this->profilePayload();
        $show = $this->request('get_series_info', ['series_id' => $showId]);

        if (!isset($show['info']) || !is_array($show['info']) || empty($show['info']['name'])) {
            throw new NotFoundException('The requested show was not found.');
        }

        $show['info']['series_id'] = (int) $showId;

        if (isset($show['episodes']) && is_array($show['episodes'])) {
            foreach ($show['episodes'] as &$episodes) {
                if (!is_array($episodes)) {
                    continue;
                }

                foreach ($episodes as &$episode) {
                    if (isset($episode['id'], $episode['container_extension'])) {
                        $episode['url'] = $this->generateStreamUrl([
                            'type' => 'episode',
                            'stream_id' => $episode['id'],
                            'extension' => $episode['container_extension'],
                        ]);
                    }
                }
                unset($episode);
            }
            unset($episodes);
        }

        return $show;
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return array<string, mixed>
     */
    public function getShortEpg(array $options): array
    {
        $channelId = $this->requiredIdentifier($options, 'channel_id', 'channelId');
        $parameters = ['stream_id' => $channelId];
        $limit = $this->option($options, 'limit');

        if ($limit !== null) {
            $parameters['limit'] = $this->positiveInteger($limit, 'limit');
        }

        return $this->request('get_short_epg', $parameters);
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return array<string, mixed>
     */
    public function getFullEpg(array $options): array
    {
        $channelId = $this->requiredIdentifier($options, 'channel_id', 'channelId');

        return $this->request('get_simple_data_table', ['stream_id' => $channelId]);
    }

    /**
     * @param array<string, mixed> $stream
     */
    public function generateStreamUrl(array $stream): string
    {
        $type = isset($stream['type']) ? $stream['type'] : null;
        $streamId = $this->requiredIdentifier($stream, 'stream_id', 'streamId');

        if (!in_array($type, ['channel', 'movie', 'episode'], true)) {
            throw new InvalidConfigurationException(
                'The stream type must be "channel", "movie", or "episode".'
            );
        }

        $username = rawurlencode($this->username);
        $password = rawurlencode($this->password);
        $streamId = rawurlencode((string) $streamId);

        if ($type === 'channel' && isset($stream['timeshift'])) {
            return $this->timeshiftUrl($streamId, $username, $password, $stream['timeshift']);
        }

        $extension = isset($stream['extension'])
            ? $this->streamExtension($stream['extension'])
            : $this->preferredFormat;

        if ($type === 'channel') {
            $extension = $this->channelFormat($extension);

            return sprintf(
                '%s/live/%s/%s/%s.%s',
                $this->baseUrl,
                $username,
                $password,
                $streamId,
                $extension
            );
        }

        $path = $type === 'movie' ? 'movie' : 'series';

        return sprintf(
            '%s/%s/%s/%s/%s.%s',
            $this->baseUrl,
            $path,
            $username,
            $password,
            $streamId,
            $extension
        );
    }

    public function getXmltvUrl(): string
    {
        return $this->baseUrl . '/xmltv.php?' . $this->authenticationQuery();
    }

    public function clearProfileCache()
    {
        $this->profilePayload = null;
    }

    /**
     * @return array<string, mixed>
     */
    private function profilePayload(): array
    {
        if ($this->profilePayload === null) {
            $this->profilePayload = $this->request(null);

            if (
                isset($this->profilePayload['user_info']['auth'])
                && (int) $this->profilePayload['user_info']['auth'] !== 1
            ) {
                $this->profilePayload = null;
                throw new AuthenticationException('The Xtream API rejected the credentials.');
            }
        }

        return $this->profilePayload;
    }

    /**
     * @param string|null          $action
     * @param array<string, mixed> $parameters
     *
     * @return array<string, mixed>
     */
    private function request($action, array $parameters = []): array
    {
        $query = [
            'username' => $this->username,
            'password' => $this->password,
        ];

        if ($action !== null && $action !== '') {
            $query['action'] = $action;
        }

        $query = array_merge($query, $parameters);
        $url = $this->baseUrl . '/player_api.php?' . http_build_query(
            $query,
            '',
            '&',
            PHP_QUERY_RFC3986
        );

        $response = $this->httpClient->get($url, ['Accept: application/json']);
        $statusCode = $response->getStatusCode();

        if ($statusCode < 200 || $statusCode >= 300) {
            throw new HttpException($statusCode);
        }

        $payload = json_decode($response->getBody(), true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($payload)) {
            throw new InvalidResponseException('The Xtream API returned invalid JSON.');
        }

        return $payload;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function listRequest(string $action): array
    {
        $items = $this->request($action);

        foreach ($items as $item) {
            if (!is_array($item)) {
                throw new InvalidResponseException('The Xtream API returned an invalid list.');
            }
        }

        return $items;
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return array<int, array<string, mixed>>
     */
    private function filterableRequest(string $action, array $options): array
    {
        $parameters = [];
        $categoryId = $this->option($options, 'category_id', 'categoryId');

        if ($categoryId !== null && $categoryId !== '') {
            $parameters['category_id'] = $categoryId;
        }

        $items = $this->request($action, $parameters);

        foreach ($items as $item) {
            if (!is_array($item)) {
                throw new InvalidResponseException('The Xtream API returned an invalid list.');
            }
        }

        $page = $this->option($options, 'page');

        if ($page === null) {
            return $items;
        }

        $page = $this->positiveInteger($page, 'page');
        $limitOption = $this->option($options, 'limit');
        $limit = $limitOption === null ? 10 : $this->positiveInteger($limitOption, 'limit');

        return array_slice($items, ($page - 1) * $limit, $limit);
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return mixed|null
     */
    private function option(array $options, string $name, string $alias = '')
    {
        if (array_key_exists($name, $options)) {
            return $options[$name];
        }

        if ($alias !== '' && array_key_exists($alias, $options)) {
            return $options[$alias];
        }

        return null;
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return int|string
     */
    private function requiredIdentifier(array $options, string $name, string $alias)
    {
        $value = $this->option($options, $name, $alias);

        if ((!is_int($value) && !is_string($value)) || (string) $value === '') {
            throw new InvalidConfigurationException(
                sprintf('The "%s" option must be a non-empty string or integer.', $name)
            );
        }

        return $value;
    }

    /**
     * @param mixed $value
     */
    private function positiveInteger($value, string $name): int
    {
        $integer = filter_var($value, FILTER_VALIDATE_INT);

        if ($integer === false || $integer < 1) {
            throw new InvalidConfigurationException(
                sprintf('The "%s" option must be a positive integer.', $name)
            );
        }

        return $integer;
    }

    /**
     * @param mixed $timeshift
     */
    private function timeshiftUrl(string $streamId, string $username, string $password, $timeshift): string
    {
        if (
            !is_array($timeshift)
            || !isset($timeshift['start'], $timeshift['duration'])
            || !$timeshift['start'] instanceof DateTimeInterface
        ) {
            throw new InvalidConfigurationException(
                'Timeshift requires a DateTimeInterface start and a positive duration.'
            );
        }

        $duration = $this->positiveInteger($timeshift['duration'], 'timeshift.duration');
        $start = gmdate('Y-m-d:H-i', $timeshift['start']->getTimestamp());

        return sprintf(
            '%s/timeshift/%s/%s/%d/%s/%s.ts',
            $this->baseUrl,
            $username,
            $password,
            $duration,
            $start,
            $streamId
        );
    }

    private function channelFormat(string $requested): string
    {
        if ($this->preferredFormat === 'rtmp') {
            return 'ts';
        }

        if (
            $this->profilePayload === null
            || !isset($this->profilePayload['user_info']['allowed_output_formats'])
            || !is_array($this->profilePayload['user_info']['allowed_output_formats'])
        ) {
            return $requested;
        }

        $allowed = $this->profilePayload['user_info']['allowed_output_formats'];

        if (in_array($requested, $allowed, true) || $allowed === []) {
            return $requested;
        }

        return $this->streamExtension($allowed[0]);
    }

    /**
     * @param mixed $value
     */
    private function streamFormat($value): string
    {
        $format = $this->streamExtension($value);

        if (!in_array($format, ['ts', 'm3u8', 'rtmp'], true)) {
            throw new InvalidConfigurationException(
                'The "preferred_format" option must be "ts", "m3u8", or "rtmp".'
            );
        }

        return $format;
    }

    /**
     * @param mixed $value
     */
    private function streamExtension($value): string
    {
        if (!is_string($value) || !preg_match('/^[a-zA-Z0-9]+$/', $value)) {
            throw new InvalidConfigurationException(
                'A stream extension must contain only letters and numbers.'
            );
        }

        return strtolower($value);
    }

    private function authenticationQuery(): string
    {
        return http_build_query(
            ['username' => $this->username, 'password' => $this->password],
            '',
            '&',
            PHP_QUERY_RFC3986
        );
    }

    private function baseUrl(string $url): string
    {
        $parts = parse_url($url);

        if (
            $parts === false
            || !isset($parts['scheme'], $parts['host'])
            || !in_array(strtolower($parts['scheme']), ['http', 'https'], true)
            || isset($parts['user'])
            || isset($parts['pass'])
            || isset($parts['query'])
            || isset($parts['fragment'])
        ) {
            throw new InvalidConfigurationException(
                'The "url" option must be an HTTP or HTTPS base URL.'
            );
        }

        return rtrim($url, '/');
    }

    /**
     * @param array<string, mixed> $options
     */
    private function requiredString(array $options, string $key): string
    {
        if (!array_key_exists($key, $options)) {
            throw new InvalidConfigurationException(
                sprintf('The "%s" option is required.', $key)
            );
        }

        return $this->nonEmptyString($options[$key], $key);
    }

    /**
     * @param mixed $value
     */
    private function nonEmptyString($value, string $key): string
    {
        if (!is_string($value) || trim($value) === '') {
            throw new InvalidConfigurationException(
                sprintf('The "%s" option must be a non-empty string.', $key)
            );
        }

        return $value;
    }
}
