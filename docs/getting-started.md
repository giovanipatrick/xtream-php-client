# Getting started

## Requirements

- PHP 7.0 or newer
- cURL extension
- JSON extension
- Composer

## Development installation

Install the package from Packagist:

```bash
composer require giovanipatrick/xtream-php-client
```

## Configure a client

```php
<?php

require __DIR__ . '/vendor/autoload.php';

use Xtream\Client;
use Xtream\Serializer\StandardizedSerializer;

$client = new Client([
    'url' => 'https://example.com',
    'username' => 'username',
    'password' => 'password',
    'preferred_format' => 'm3u8',
    'serializer' => new StandardizedSerializer(),
]);
```

Never commit real credentials. Load them from environment variables or a
secret manager in applications.

## Make requests

```php
$profile = $client->getProfile();
$categories = $client->getChannelCategories();
$channels = $client->getChannels(['category_id' => 10]);
```

Omit `serializer` to receive raw provider arrays. Continue to the
[API reference](api-reference.md) for every serializer, endpoint, and option.
