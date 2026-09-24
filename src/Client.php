<?php

declare(strict_types=1);

namespace Xtream;

use Xtream\Exception\InvalidConfigurationException;

/**
 * Entry point for an Xtream-compatible Player API.
 *
 * Network operations will be introduced incrementally. The initial client
 * validates and safely stores connection configuration without exposing the
 * password through its public API.
 */
final class Client
{
    /** @var string */
    private $baseUrl;

    /** @var string */
    private $username;

    /** @var string */
    private $password;

    /** @var string */
    private $preferredFormat;

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(array $options)
    {
        $this->baseUrl = $this->requiredString($options, 'url');
        $this->username = $this->requiredString($options, 'username');
        $this->password = $this->requiredString($options, 'password');
        $this->preferredFormat = isset($options['preferred_format'])
            ? $this->nonEmptyString($options['preferred_format'], 'preferred_format')
            : 'm3u8';

        $this->baseUrl = rtrim($this->baseUrl, '/');
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function getPreferredFormat(): string
    {
        return $this->preferredFormat;
    }

    /**
     * @param array<string, mixed> $options
     */
    private function requiredString(array $options, string $key): string
    {
        if (!array_key_exists($key, $options)) {
            throw new InvalidConfigurationException(
                sprintf('The "%s" option is required.', $key)
            );
        }

        return $this->nonEmptyString($options[$key], $key);
    }

    /**
     * @param mixed $value
     */
    private function nonEmptyString($value, string $key): string
    {
        if (!is_string($value) || trim($value) === '') {
            throw new InvalidConfigurationException(
                sprintf('The "%s" option must be a non-empty string.', $key)
            );
        }

        return $value;
    }
}
