# Integration tests

Integration test source may be committed when it contains no credentials,
provider-specific values, API responses, playlist content, generated stream
URLs, or private fixtures.

Live tests must:

1. Read `XTREAM_BASE_URL`, `XTREAM_USERNAME`, and `XTREAM_PASSWORD` from the
   process environment.
2. Skip cleanly when any required variable is missing.
3. Avoid including credentials or complete request URLs in assertion messages,
   exceptions, snapshots, or logs.
4. Avoid persisting provider responses unless they are manually sanitized and
   moved to a public fixture directory.
5. Run only through an explicit local command, never in public CI.

Copy `.env.example` to `.env`, fill it locally, and load it in the shell before
running the integration suite. The `.env` file is ignored by Git.
