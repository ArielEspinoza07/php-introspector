<?php

declare(strict_types=1);

namespace Introspector\VOs\Methods;

use Introspector\VOs\Attributes\AttributeMetadata;
use Introspector\VOs\DocBlocks\DocBlockMetadata;
use Introspector\VOs\Modifiers\MethodModifier;
use Introspector\VOs\Parameters\ParameterMetadata;
use Introspector\VOs\Shared\DeclaringSource;
use Introspector\VOs\Shared\LinesMetadata;
use Introspector\VOs\Types\TypeMetadata;
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
        public DeclaringSource $declaringSource,
        public ?LinesMetadata $lines = null,
        public ?DocBlockMetadata $docBlock = null,
        public ?TypeMetadata $returnType = null,
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
            'name' => $this->name,
            'modifier' => $this->modifier->toArray(),
            'declaring_source' => $this->declaringSource->toArray(),
            'lines' => $this->lines?->toArray(),
            'doc_block' => $this->docBlock?->toArray(),
            'return_type' => $this->returnType?->toArray(),
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
