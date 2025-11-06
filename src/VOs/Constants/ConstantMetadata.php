<?php

declare(strict_types=1);

namespace Aurora\Reflection\VOs\Constants;

use Aurora\Reflection\Enums\Visibility;
use Aurora\Reflection\VOs\DocBlocks\DocBlockMetadata;
use Aurora\Reflection\VOs\Shared\DeclaringSource;
use Aurora\Reflection\VOs\Types\TypeMetadata;
use JsonSerializable;

final readonly class ConstantMetadata implements JsonSerializable
{
    public function __construct(
        public string $name,
        public mixed $value,
        public Visibility $visibility,
        public DeclaringSource $declaringSource,
        public bool $isFinal = false,
        public ?TypeMetadata $type = null,
        public ?DocBlockMetadata $docBlock = null,
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'value' => $this->value,
            'visibility' => $this->visibility,
            'declaring_source' => $this->declaringSource->toArray(),
            'is_final' => $this->isFinal,
            'type' => $this->type,
            'doc_block' => $this->docBlock?->toArray(),
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
