# API reference

## `Xtream\Client`

The client communicates with `player_api.php` and returns raw associative
arrays by default. Optional serializers transform the response after stream
URLs and pagination are applied. Credentials are URL-encoded and are never
included in library-generated exception messages.

## Constructor

```php
new Client(array $options)
```

Required options:

| Option | Type | Description |
| --- | --- | --- |
| `url` | `string` | HTTP(S) provider base URL without `player_api.php`. |
| `username` | `string` | Player API username. |
| `password` | `string` | Player API password. |

Optional options:

| Option | Type | Default | Description |
| --- | --- | --- | --- |
| `preferred_format` | `string` | `ts` | `ts`, `m3u8`, or `rtmp`. |
| `timeout` | `int` | `30` | Complete request timeout in seconds. |
| `connect_timeout` | `int` | `10` | Connection timeout in seconds. |
| `verify_ssl` | `bool` | `true` | Verify the provider TLS certificate. |
| `follow_redirects` | `bool` | `false` | Follow at most three HTTP redirects. |
| `user_agent` | `string` | package identifier | HTTP User-Agent value. |
| `http_client` | `HttpClientInterface` | cURL client | Custom injectable transport. |
| `serializer` | `SerializerInterface` | raw responses | Response serializer. |

Disabling TLS verification should be limited to controlled development
environments. Redirects are disabled by default because authentication is sent
in the request query.

## Serializers

```php
use Xtream\Serializer\CamelCaseSerializer;
use Xtream\Serializer\StandardizedSerializer;
use Xtream\Serializer\JsonApiSerializer;

$client = new Client([
    // credentials and other options
    'serializer' => new StandardizedSerializer(),
]);

echo $client->getSerializerType(); // Standardized
```

`CamelCaseSerializer` changes response keys while preserving provider values.
`StandardizedSerializer` normalizes names, identifiers, booleans, lists and
dates; dates are represented by `DateTimeImmutable`. `JsonApiSerializer`
returns JSON:API resource objects, relationships and included show resources;
dates are ISO 8601 strings ready for JSON encoding.

Custom serializers can replace only selected resources. Unspecified resources
remain raw:

```php
use Xtream\Serializer\CallbackSerializer;

$serializer = new CallbackSerializer('Application', [
    'channels' => static function (array $channels): array {
        return array_map(static function (array $channel): array {
            return [
                'id' => (string) $channel['stream_id'],
                'label' => $channel['name'],
            ];
        }, $channels);
    },
]);
```

Callback keys are `profile`, `serverInfo`, `channelCategories`,
`movieCategories`, `showCategories`, `channels`, `movies`, `movie`, `shows`,
`show`, `shortEPG`, and `fullEPG`. Callbacks must return arrays.

## Account

```php
$profile = $client->getProfile();
$server = $client->getServerInfo();
$client->clearProfileCache();
```

Profile and server information share one cached authentication request.
Operations that require output-format information also populate this cache.

## Categories

```php
$liveCategories = $client->getChannelCategories();
$movieCategories = $client->getMovieCategories();
$showCategories = $client->getShowCategories();
```

## Listings

```php
$channels = $client->getChannels([
    'category_id' => 10,
    'page' => 1,
    'limit' => 50,
]);

$movies = $client->getMovies(['category_id' => 20]);
$shows = $client->getShows(['category_id' => 30]);
```

`categoryId` is accepted as an alias for `category_id`. Pagination is local
because common Xtream-compatible Player APIs return the complete category.
When `page` is supplied without `limit`, the limit defaults to 10.

Channel and movie listings receive a generated `url` field when the required
stream identifiers are present.

## Details

```php
$movie = $client->getMovie(['movie_id' => 123]);
$show = $client->getShow(['show_id' => 456]);
```

`movieId` and `showId` camelCase aliases are also accepted. Movie results
receive a top-level `url`. Each show episode receives its own `url`, and the
requested series ID is added to `show['info']['series_id']`.

## EPG

```php
$short = $client->getShortEpg([
    'channel_id' => 789,
    'limit' => 5,
]);

$full = $client->getFullEpg(['channel_id' => 789]);
```

`channelId` is accepted as an alias for `channel_id`.

## Stream URLs

```php
$liveUrl = $client->generateStreamUrl([
    'type' => 'channel',
    'stream_id' => 789,
    'extension' => 'm3u8',
]);

$movieUrl = $client->generateStreamUrl([
    'type' => 'movie',
    'stream_id' => 123,
    'extension' => 'mkv',
]);

$episodeUrl = $client->generateStreamUrl([
    'type' => 'episode',
    'stream_id' => 456,
    'extension' => 'mp4',
]);
```

For channels, an unsupported requested format falls back to the first format
listed in the cached user profile. A preferred `rtmp` format follows the
reference library behavior and generates a `.ts` live URL.

Timeshift uses a `DateTimeInterface` value. The timestamp is converted to UTC:

```php
$timeshiftUrl = $client->generateStreamUrl([
    'type' => 'channel',
    'stream_id' => 789,
    'timeshift' => [
        'start' => new DateTimeImmutable('2026-09-24 21:30:00 UTC'),
        'duration' => 60,
    ],
]);
```

The XMLTV endpoint can be generated with:

```php
$xmltvUrl = $client->getXmltvUrl();
```

Generated media and XMLTV URLs contain the Player API credentials by protocol
design. Treat them as secrets and never log or commit them.

## Exceptions

All runtime library exceptions inherit from `Xtream\Exception\XtreamException`:

| Exception | Meaning |
| --- | --- |
| `AuthenticationException` | The provider rejected the account. |
| `HttpException` | A non-2xx HTTP response was received. |
| `NetworkException` | cURL could not complete the request. |
| `InvalidResponseException` | JSON or response structure was invalid. |
| `NotFoundException` | A requested movie or show was not found. |

Invalid caller options raise `InvalidConfigurationException`, which extends
PHP's `InvalidArgumentException`.
