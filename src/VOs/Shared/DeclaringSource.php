<?php

declare(strict_types=1);

namespace Aurora\Reflection\VOs\Shared;

use Aurora\Reflection\Enums\SourceType;
use JsonSerializable;

final readonly class DeclaringSource implements JsonSerializable
{
    public function __construct(
        public SourceType $type,
        public string $className,
        public string $shortName,
        public string $namespace,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type->value,
            'class_name' => $this->className,
            'short_name' => $this->shortName,
            'namespace' => $this->namespace,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
