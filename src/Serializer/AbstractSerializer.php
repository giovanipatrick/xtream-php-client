<?php

declare(strict_types=1);

namespace Xtream\Serializer;

use DateTimeImmutable;
use Exception;

abstract class AbstractSerializer implements SerializerInterface
{
    /**
     * @param array<mixed> $payload
     *
     * @return array<mixed>
     */
    protected function camelize(array $payload, bool $deep = false): array
    {
        if ($this->isList($payload)) {
            $result = [];

            foreach ($payload as $value) {
                $result[] = is_array($value)
                    ? $this->camelizeMap($value, $deep)
                    : $value;
            }

            return $result;
        }

        return $this->camelizeMap($payload, $deep);
    }

    /**
     * @param array<mixed> $payload
     *
     * @return array<mixed>
     */
    private function camelizeMap(array $payload, bool $deep): array
    {
        $result = [];

        foreach ($payload as $key => $value) {
            $camelKey = is_string($key) ? $this->camelCase($key) : $key;

            if ($deep && is_array($value)) {
                $value = $this->camelize($value, true);
            }

            $result[$camelKey] = $value;
        }

        return $result;
    }

    private function camelCase(string $key): string
    {
        $camel = preg_replace_callback(
            '/[_-]+([a-zA-Z0-9])/',
            static function (array $matches): string {
                return strtoupper($matches[1]);
            },
            $key
        );

        return $camel === null ? $key : $camel;
    }

    /**
     * @param array<mixed> $values
     */
    protected function isList(array $values): bool
    {
        return $values === [] || array_keys($values) === range(0, count($values) - 1);
    }

    /**
     * @param array<string, mixed> $values
     * @param mixed                $default
     *
     * @return mixed
     */
    protected function value(array $values, string $key, $default = null)
    {
        return array_key_exists($key, $values) ? $values[$key] : $default;
    }

    /**
     * @param mixed $value
     *
     * @return array<int, string>
     */
    protected function stringList($value): array
    {
        if (!is_array($value)) {
            return [];
        }

        return array_map('strval', array_values($value));
    }

    /**
     * @param mixed $value
     *
     * @return array<int, string>
     */
    protected function csv($value): array
    {
        if (!is_string($value) || trim($value) === '') {
            return [];
        }

        return array_values(array_filter(array_map('trim', explode(',', $value)), 'strlen'));
    }

    /**
     * @param mixed $value
     */
    protected function timestampDate($value): DateTimeImmutable
    {
        return new DateTimeImmutable('@' . (string) ((int) $value));
    }

    /**
     * @param mixed $value
     */
    /**
     * @return DateTimeImmutable|null
     */
    protected function optionalDate($value)
    {
        if (!is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            return new DateTimeImmutable($value);
        } catch (Exception $exception) {
            return null;
        }
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    protected function decoded($value)
    {
        if (!is_string($value)) {
            return $value;
        }

        $decoded = base64_decode($value, true);

        return $decoded === false ? $value : $decoded;
    }
}
