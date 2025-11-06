<?php

declare(strict_types=1);

namespace Aurora\Reflection\VOs\Constructors;

use Aurora\Reflection\VOs\Attributes\AttributeMetadata;
use Aurora\Reflection\VOs\DocBlocks\DocBlockMetadata;
use Aurora\Reflection\VOs\Modifiers\ConstructorModifier;
use Aurora\Reflection\VOs\Parameters\ParameterMetadata;
use JsonSerializable;

final readonly class ConstructorMetadata implements JsonSerializable
{
    /**
     * @param  list<ParameterMetadata>  $parameters
     * @param  list<AttributeMetadata>  $attributes
     */
    public function __construct(
        public ConstructorModifier $modifier,
        public ?DocBlockMetadata $docBlock = null,
        public array $parameters = [],
        public array $attributes = [],
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'modifier' => $this->modifier->toArray(),
            'doc_block' => $this->docBlock?->toArray(),
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
