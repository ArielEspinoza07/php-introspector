<?php

declare(strict_types=1);

namespace Introspector\VOs\Classes;

use Introspector\VOs\Attributes\AttributeMetadata;
use Introspector\VOs\DocBlocks\DocBlockMetadata;
use Introspector\VOs\Modifiers\ClassModifier;
use Introspector\Enums\ClassType;
use Introspector\VOs\Shared\DeclaringSource;
use Introspector\VOs\Shared\LinesMetadata;
use JsonSerializable;

final readonly class ClassMetadata implements JsonSerializable
{
    /**
     * @param  list<DeclaringSource>  $implements
     * @param  list<DeclaringSource>  $traits
     * @param  list<AttributeMetadata>  $attributes
     */
    public function __construct(
        public string $name,
        public string $shortName,
        public string $nameSpace,
        public string $file,
        public ClassType $type,
        public ClassModifier $modifier,
        public ?LinesMetadata $lines = null,
        public ?DocBlockMetadata $docBlock = null,
        public ?string $extends = null,
        public array $implements = [],
        public array $traits = [],
        public array $attributes = [],
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'short_name' => $this->shortName,
            'namespace' => $this->nameSpace,
            'file' => $this->file,
            'type' => $this->type->value,
            'lines' => $this->lines?->toArray(),
            'modifier' => $this->modifier->toArray(),
            'doc_block' => $this->docBlock?->toArray(),
            'extends' => $this->extends,
            'implements' => $this->implements,
            'traits' => $this->traits,
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
