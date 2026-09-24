# Contributing

Thank you for helping improve Xtream PHP Client.

## Branch workflow

The `main` branch contains reviewed and tested code only. Create a dedicated
branch from the latest `main` and submit a pull request when the change is
complete.

Recommended branch prefixes are `feat/`, `fix/`, `docs/`, `test/`, `refactor/`,
and `chore/`. Codex-created branches use the required `codex/` prefix.

## Commits

Every commit message must be written in English and follow the project's
Conventional Commits convention:

```text
<emoji> <type>(optional-scope): <short imperative description>
```

Common combinations include:

| Change | Format |
| --- | --- |
| Feature | `✨ feat: add channel categories` |
| Bug fix | `🐛 fix: handle empty EPG responses` |
| Documentation | `📚 docs: explain custom serializers` |
| Tests | `✅ test: cover timeshift URLs` |
| Build | `📦 build: update development tools` |
| Refactor | `♻️ refactor: extract URL builder` |
| Maintenance | `🔧 chore: update repository metadata` |
| CI | `🧱 ci: test PHP 8.5` |

The allowed types are `feat`, `fix`, `docs`, `test`, `build`, `perf`, `style`,
`refactor`, `chore`, `ci`, `raw`, `cleanup`, `remove`, and `init`.

The repository includes a versioned `commit-msg` hook. Enable it once after
cloning:

```bash
git config core.hooksPath .githooks
```

## Quality checks

```bash
composer install
composer check
```

New behavior requires unit tests. Unit tests must use sanitized fixtures and
must never depend on a real provider.

## Live-provider testing

Integration test source may be committed under `tests/Integration/`, provided
it contains no provider-specific or sensitive data. Supply credentials only
through local environment variables loaded from an ignored `.env` file.
Public CI must not run live-provider tests.

Never include real provider URLs, usernames, passwords, playlist content, API
responses, generated stream URLs, private fixtures, or test output in a commit,
issue, pull request, or CI log.

## Pull requests

- Keep each pull request focused on one concern.
- Update both README translations when public usage changes.
- Update `CHANGELOG.md` under `Unreleased` for user-visible changes.
- Confirm all local checks pass.
- Wait for review and CI approval before merging into `main`.

## Versioning and releases

This project follows Semantic Versioning:

- `PATCH` fixes backward-compatible defects.
- `MINOR` adds backward-compatible functionality.
- `MAJOR` introduces backward-incompatible changes.

Before `1.0.0`, breaking changes may occur in minor releases and must be
described clearly in the changelog. Releases are created only from reviewed
`main` commits using signed tags named `vX.Y.Z`.
