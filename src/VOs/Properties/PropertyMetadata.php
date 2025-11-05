<?php

declare(strict_types=1);

namespace Aurora\Reflection\VOs\Properties;

use Aurora\Reflection\VOs\Attributes\AttributeMetadata;
use Aurora\Reflection\VOs\DocBlocks\DocBlockMetadata;
use Aurora\Reflection\VOs\Modifiers\PropertyModifier;
use Aurora\Reflection\VOs\Shared\DeclaringSource;
use Aurora\Reflection\VOs\Types\TypeMetadata;
use JsonSerializable;

final readonly class PropertyMetadata implements JsonSerializable
{
    /**
     * @param  list<AttributeMetadata>  $attributes
     */
    public function __construct(
        public string $name,
        public PropertyModifier $modifier,
        public bool $hasDefaultValue,
        public mixed $defaultValue,
        public DeclaringSource $declaringSource,
        public ?DocBlockMetadata $docBlock = null,
        public ?TypeMetadata $type = null,
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
            'has_default_value' => $this->hasDefaultValue,
            'default_value' => $this->defaultValue,
            'declaring_source' => $this->declaringSource->toArray(),
            'doc_block' => $this->docBlock?->toArray(),
            'type' => $this->type?->toArray(),
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
