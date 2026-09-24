# Xtream PHP Client

[![CI](https://github.com/giovanipatrick/xtream-php-client/actions/workflows/ci.yml/badge.svg)](https://github.com/giovanipatrick/xtream-php-client/actions/workflows/ci.yml)
[![Documentation](https://github.com/giovanipatrick/xtream-php-client/actions/workflows/docs.yml/badge.svg)](https://github.com/giovanipatrick/xtream-php-client/actions/workflows/docs.yml)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](LICENSE)

English | [Português do Brasil](README.pt-BR.md)

A PHP 7.0+ client for Xtream-compatible IPTV Player APIs.

> This project is under active development. The public API may change before
> the first stable `1.0.0` release.

## Goals

- Support live TV, VOD, series, episodes, and EPG data.
- Generate live, movie, episode, and timeshift stream URLs.
- Normalize inconsistent responses from Xtream-compatible providers.
- Offer raw, camelCase, standardized, JSON:API, and custom serializers.
- Remain compatible with PHP 7.0 through current PHP versions.
- Keep the runtime lightweight and framework-independent.

## Requirements

- PHP 7.0 or newer
- JSON extension
- Composer

The HTTP transport will use cURL when Player API operations are introduced.

## Installation

The package has not been released on Packagist yet. During development, add
the repository as a VCS dependency:

```bash
composer config repositories.xtream-php-client vcs https://github.com/giovanipatrick/xtream-php-client
composer require giovanipatrick/xtream-php-client:dev-main
```

Tagged releases will later be installable with:

```bash
composer require giovanipatrick/xtream-php-client
```

## Current usage

The first development milestone provides configuration validation. Network
methods will be added in subsequent milestones.

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

## Development

Install development dependencies and run all checks:

```bash
composer install
composer check
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
