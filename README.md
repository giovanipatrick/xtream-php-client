# Xtream PHP Client

[![CI](https://github.com/giovanipatrick/xtream-php-client/actions/workflows/ci.yml/badge.svg)](https://github.com/giovanipatrick/xtream-php-client/actions/workflows/ci.yml)
[![Documentation](https://github.com/giovanipatrick/xtream-php-client/actions/workflows/docs.yml/badge.svg)](https://github.com/giovanipatrick/xtream-php-client/actions/workflows/docs.yml)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)

English | [Português do Brasil](README.pt-BR.md)

A PHP 7.0+ client for Xtream-compatible IPTV Player APIs.

> This project is under active development. The public API may change before
> the first stable `1.0.0` release.

## Features

- Account profile and server information.
- Live TV, VOD, series, episodes, and EPG endpoints.
- Category filters and local pagination.
- Live, movie, episode, timeshift, and XMLTV URL generation.
- URL-safe credentials and exceptions that do not expose responses or secrets.
- Injectable HTTP transport for deterministic tests.
- Remain compatible with PHP 7.0 through current PHP versions.
- Keep the runtime lightweight and framework-independent.

Response serializers and normalized data models are planned for a later
development milestone. Current methods return raw associative arrays.

## Requirements

- PHP 7.0 or newer
- cURL extension
- JSON extension
- Composer

## Installation

Install the package from Packagist:

```bash
composer require giovanipatrick/xtream-php-client
```

## Usage

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

$profile = $client->getProfile();
$server = $client->getServerInfo();

$channels = $client->getChannels([
    'category_id' => 10,
    'page' => 1,
    'limit' => 50,
]);

$movies = $client->getMovies(['category_id' => 20]);
$movie = $client->getMovie(['movie_id' => 123]);
$shows = $client->getShows(['category_id' => 30]);
$show = $client->getShow(['show_id' => 456]);

$shortEpg = $client->getShortEpg(['channel_id' => 789, 'limit' => 5]);
$fullEpg = $client->getFullEpg(['channel_id' => 789]);
```

Both `snake_case` option names and their camelCase counterparts from the
TypeScript inspiration are accepted. See the
[API reference](https://giovanipatrick.github.io/xtream-php-client/api-reference/)
for transport options, URL generation, and error behavior.

## Development

Install development dependencies and run all checks:

```bash
composer install
composer check
```

To run the opt-in live-provider suite, export the variables from a local
ignored `.env` file and run:

```bash
composer test:integration
```

Unit tests must not depend on a real IPTV provider. Integration test source may
be committed, but credentials, private fixtures, provider responses, generated
stream URLs, and test output are local-only and ignored by Git. Public CI does
not run live-provider tests.

## Workflow and releases

- `main` contains reviewed and tested code only.
- Work starts in a dedicated feature, fix, documentation, or maintenance branch.
- Pull requests must pass the complete PHP compatibility matrix.
- Commits use Conventional Commits, are written in English, and may include an
  optional matching emoji as described in [CONTRIBUTING.md](CONTRIBUTING.md).
- Releases follow Semantic Versioning and are created from signed `vX.Y.Z` tags.

See the [documentation site](https://giovanipatrick.github.io/xtream-php-client/)
and [contribution guide](CONTRIBUTING.md) for details.

## Acknowledgements

This project is inspired by
[`@iptv/xtream-api`](https://github.com/ektotv/xtream-api). It is an independent
PHP implementation and is not affiliated with Xtream Codes or IPTV providers.

## License

Released under the [MIT License](LICENSE).
