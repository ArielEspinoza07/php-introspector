<?php

declare(strict_types=1);

namespace Aurora\Reflection\VOs\Methods;

use Aurora\Reflection\VOs\Attributes\AttributeMetadata;
use Aurora\Reflection\VOs\Modifiers\MethodModifier;
use Aurora\Reflection\VOs\Parameters\ParameterMetadata;
use JsonSerializable;

final readonly class MethodMetadata implements JsonSerializable
{
    /**
     * @param  list<ParameterMetadata>  $parameters
     * @param  list<AttributeMetadata>  $attributes
     */
    public function __construct(
        public string $name,
        public MethodModifier $modifier,
        public ?string $returnType = null,
        public array $parameters = [],
        public array $attributes = [],
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'modifier' => $this->modifier->toArray(),
            'return_type' => $this->returnType,
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
