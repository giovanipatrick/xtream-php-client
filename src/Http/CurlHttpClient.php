<?php

declare(strict_types=1);

namespace Xtream\Http;

use Xtream\Exception\InvalidConfigurationException;
use Xtream\Exception\NetworkException;

final class CurlHttpClient implements HttpClientInterface
{
    /** @var int */
    private $timeout;

    /** @var int */
    private $connectTimeout;

    /** @var bool */
    private $verifySsl;

    /** @var bool */
    private $followRedirects;

    /** @var string */
    private $userAgent;

    /**
     * @param array<string, mixed> $options
     */
    public function __construct(array $options = [])
    {
        $this->timeout = $this->positiveInteger($options, 'timeout', 30);
        $this->connectTimeout = $this->positiveInteger($options, 'connect_timeout', 10);
        $this->verifySsl = $this->boolean($options, 'verify_ssl', true);
        $this->followRedirects = $this->boolean($options, 'follow_redirects', false);
        $this->userAgent = isset($options['user_agent'])
            ? $this->nonEmptyString($options['user_agent'], 'user_agent')
            : 'xtream-php-client/development';
    }

    public function get(string $url, array $headers = []): Response
    {
        $handle = curl_init();

        if ($handle === false) {
            throw new NetworkException('The HTTP client could not be initialized.');
        }

        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_HTTPGET => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => $this->connectTimeout,
            CURLOPT_SSL_VERIFYPEER => $this->verifySsl,
            CURLOPT_SSL_VERIFYHOST => $this->verifySsl ? 2 : 0,
            CURLOPT_FOLLOWLOCATION => $this->followRedirects,
            CURLOPT_MAXREDIRS => $this->followRedirects ? 3 : 0,
            CURLOPT_USERAGENT => $this->userAgent,
        ];

        if (defined('CURLOPT_PROTOCOLS') && defined('CURLPROTO_HTTP') && defined('CURLPROTO_HTTPS')) {
            $options[CURLOPT_PROTOCOLS] = CURLPROTO_HTTP | CURLPROTO_HTTPS;
        }

        if (defined('CURLOPT_REDIR_PROTOCOLS') && defined('CURLPROTO_HTTP') && defined('CURLPROTO_HTTPS')) {
            $options[CURLOPT_REDIR_PROTOCOLS] = CURLPROTO_HTTP | CURLPROTO_HTTPS;
        }

        curl_setopt_array($handle, $options);
        $body = curl_exec($handle);

        if ($body === false) {
            $errorNumber = curl_errno($handle);
            curl_close($handle);

            throw new NetworkException(
                sprintf('The Xtream API request failed (cURL error %d).', $errorNumber)
            );
        }

        $statusCode = (int) curl_getinfo($handle, CURLINFO_HTTP_CODE);
        curl_close($handle);

        return new Response($statusCode, $body);
    }

    /**
     * @param array<string, mixed> $options
     */
    private function positiveInteger(array $options, string $key, int $default): int
    {
        if (!array_key_exists($key, $options)) {
            return $default;
        }

        $value = filter_var($options[$key], FILTER_VALIDATE_INT);

        if ($value === false || $value < 1) {
            throw new InvalidConfigurationException(
                sprintf('The "%s" option must be a positive integer.', $key)
            );
        }

        return $value;
    }

    /**
     * @param array<string, mixed> $options
     */
    private function boolean(array $options, string $key, bool $default): bool
    {
        if (!array_key_exists($key, $options)) {
            return $default;
        }

        if (!is_bool($options[$key])) {
            throw new InvalidConfigurationException(
                sprintf('The "%s" option must be a boolean.', $key)
            );
        }

        return $options[$key];
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

        return trim($value);
    }
}
