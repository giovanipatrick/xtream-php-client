# Xtream PHP Client

Xtream PHP Client is a framework-independent PHP 7.0+ client for
Xtream-compatible IPTV Player APIs.

!!! warning "Development status"
    The project is currently in pre-release development. Public APIs may change
    before version 1.0.0.

The client covers account and server information, live TV, VOD, series,
episodes, EPG data, stream URL generation, and pagination. Response
normalization and serializers are planned for the next milestone.

## Design principles

- Compatible with PHP 7.0 and current PHP releases.
- Safe defaults and explicit error handling.
- No framework coupling.
- Provider credentials never appear in logs or exceptions.
- Predictable normalized data without losing access to raw responses.

Continue with [Getting started](getting-started.md), or read this page in
[Brazilian Portuguese](pt-BR/index.md).
