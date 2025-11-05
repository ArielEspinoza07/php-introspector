<?php

declare(strict_types=1);

namespace Aurora\Reflection\VOs\Constants;

use Aurora\Reflection\Enums\Visibility;
use Aurora\Reflection\VOs\DocBlocks\DocBlockMetadata;
use Aurora\Reflection\VOs\Types\TypeMetadata;
use JsonSerializable;

final readonly class ConstantMetadata implements JsonSerializable
{
    public function __construct(
        public string $name,
        public mixed $value,
        public Visibility $visibility,
        public bool $isFinal = false,
        public bool $isReadOnly = false,
        public ?TypeMetadata $type = null,
        public ?DocBlockMetadata $docBlock = null,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'value' => $this->value,
            'visibility' => $this->visibility,
            'is_final' => $this->isFinal,
            'is_read_only' => $this->isReadOnly,
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
