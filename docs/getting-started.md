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

$client = new Client([
    'url' => 'https://example.com',
    'username' => 'username',
    'password' => 'password',
    'preferred_format' => 'm3u8',
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

Methods currently return raw associative arrays from the provider. Continue to
the [API reference](api-reference.md) for every supported endpoint and option.
