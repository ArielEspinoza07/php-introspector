<?php

declare(strict_types=1);

namespace Aurora\Reflection\VOs\Classes;

use Aurora\Reflection\VOs\AttributeMetadata;
use JsonSerializable;

final readonly class ClassMetadata implements JsonSerializable
{
    /**
     * @param  list<string>  $implements
     * @param  list<string>  $traits
     * @param  list<AttributeMetadata>  $attributes
     */
    public function __construct(
        public string $name,
        public string $shortName,
        public string $nameSpace,
        public string $file,
        public LinesMetadata $lines,
        public ClassModifier $modifier,
        public ?string $extends = null,
        public array $implements = [],
        public array $traits = [],
        public array $attributes = [],
    ) {}

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
            'lines' => $this->lines->toArray(),
            'modifier' => $this->modifier->toArray(),
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
