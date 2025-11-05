<?php

declare(strict_types=1);

namespace Aurora\Reflection\VOs;

use Aurora\Reflection\VOs\Parameters\ParameterMetadata;
use JsonSerializable;

final readonly class ConstructorMetadata implements JsonSerializable
{
    /**
     * @param  list<ParameterMetadata>  $parameters
     * @param  list<AttributeMetadata>  $attributes
     */
    public function __construct(
        public bool $isPublic,
        public bool $isProtected,
        public bool $isPrivate,
        public array $parameters = [],
        public array $attributes = [],
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'is_public' => $this->isPublic,
            'is_protected' => $this->isProtected,
            'is_private' => $this->isPrivate,
            'parameters' => $this->parameters,
            'attributes' => $this->attributes,
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
