<?php

declare(strict_types=1);

namespace Introspector\VOs\Attributes;

use JsonSerializable;

final readonly class AttributeMetadata implements JsonSerializable
{
    /**
     * @param  array<string, mixed>  $arguments
     */
    public function __construct(
        public string $name,
        public string $fqcn,
        public string $namespace,
        public array $arguments,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'fqcn' => $this->fqcn,
            'namespace' => $this->namespace,
            'arguments' => $this->arguments,
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
