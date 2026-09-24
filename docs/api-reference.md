# API reference

## `Xtream\Client`

The client currently validates and stores its configuration. Player API
operations will be introduced incrementally and documented here as they become
available.

### Constructor

```php
new Client(array $options)
```

Required options:

| Option | Type | Description |
| --- | --- | --- |
| `url` | `string` | Provider base URL without `player_api.php`. |
| `username` | `string` | Player API username. |
| `password` | `string` | Player API password. |

Optional options:

| Option | Type | Default | Description |
| --- | --- | --- | --- |
| `preferred_format` | `string` | `m3u8` | Preferred live stream format. |

### Configuration accessors

```php
$client->getBaseUrl();
$client->getPreferredFormat();
```

The password is deliberately not exposed through a public accessor.

