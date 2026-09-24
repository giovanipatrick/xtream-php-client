# Development

## Install dependencies

```bash
composer install
```

## Run checks

```bash
composer check
```

This runs the PSR-12 coding-standard check and unit tests. CI repeats these
checks on every supported PHP version.

## Commit messages

Commits must be written in English and follow the emoji-prefixed Conventional
Commits format documented in `CONTRIBUTING.md`.

Enable the local validator:

```bash
git config core.hooksPath .githooks
```

## Live-provider tests

Integration test source may be committed when it contains no sensitive or
provider-specific data. Credentials must come from an ignored local `.env`
file. Provider responses, generated stream URLs, private fixtures, and test
output must not appear in Git or public CI.
