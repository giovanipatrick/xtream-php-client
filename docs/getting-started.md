# Getting started

## Requirements

- PHP 7.0 or newer
- JSON extension
- Composer

## Development installation

Until the first Packagist release, install directly from the Git repository:

```bash
composer config repositories.xtream-php-client vcs https://github.com/giovanipatrick/xtream-php-client
composer require giovanipatrick/xtream-php-client:dev-main
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

