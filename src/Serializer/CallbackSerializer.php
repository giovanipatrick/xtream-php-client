<?php

declare(strict_types=1);

namespace Xtream\Serializer;

use InvalidArgumentException;

final class CallbackSerializer implements SerializerInterface
{
    /** @var string */
    private $type;

    /** @var array<string, callable> */
    private $callbacks;

    /**
     * @param array<string, callable> $callbacks
     */
    public function __construct(string $type, array $callbacks)
    {
        if (trim($type) === '') {
            throw new InvalidArgumentException('The serializer type cannot be empty.');
        }

        foreach ($callbacks as $resource => $callback) {
            if (!is_string($resource) || !is_callable($callback)) {
                throw new InvalidArgumentException('Serializer callbacks must be keyed callables.');
            }
        }

        $this->type = $type;
        $this->callbacks = $callbacks;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function serialize(string $resource, array $payload): array
    {
        if (!isset($this->callbacks[$resource])) {
            return $payload;
        }

        $result = call_user_func($this->callbacks[$resource], $payload);

        if (!is_array($result)) {
            throw new InvalidArgumentException('Serializer callbacks must return an array.');
        }

        return $result;
    }
}
