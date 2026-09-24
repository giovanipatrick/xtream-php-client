<?php

declare(strict_types=1);

namespace Xtream\Serializer;

final class CamelCaseSerializer extends AbstractSerializer
{
    public function getType(): string
    {
        return 'Camel Case';
    }

    public function serialize(string $resource, array $payload): array
    {
        return $this->camelize(
            $payload,
            in_array($resource, ['movie', 'show', 'shortEPG', 'fullEPG'], true)
        );
    }
}
