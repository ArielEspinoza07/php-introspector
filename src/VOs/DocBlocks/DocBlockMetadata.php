<?php

declare(strict_types=1);

namespace Introspector\VOs\DocBlocks;

use JsonSerializable;

final readonly class DocBlockMetadata implements JsonSerializable
{
    /**
     * @param  list<ParamTag>  $params
     * @param  list<ThrowsTag>  $throws
     * @param  list<CustomTag>  $custom
     */
    public function __construct(
        public ?string $summary = null,
        public ?string $description = null,
        public array $params = [],
        public ?ReturnTag $return = null,
        public ?VarTag $var = null,
        public array $throws = [],
        public array $custom = [],
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'summary' => $this->summary,
            'description' => $this->description,
            'params' => array_map(fn (ParamTag $tag) => $tag->toArray(), $this->params),
            'return' => $this->return?->toArray(),
            'var' => $this->var?->toArray(),
            'throws' => array_map(fn (ThrowsTag $tag) => $tag->toArray(), $this->throws),
            'custom' => array_map(fn (CustomTag $custom) => $custom->toArray(), $this->custom),
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
