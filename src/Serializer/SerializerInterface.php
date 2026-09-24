<?php

declare(strict_types=1);

namespace Xtream\Serializer;

interface SerializerInterface
{
    public function getType(): string;

    /**
     * @param array<mixed> $payload
     *
     * @return array<mixed>
     */
    public function serialize(string $resource, array $payload): array;
}
