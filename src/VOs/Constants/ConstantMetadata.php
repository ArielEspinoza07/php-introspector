<?php

declare(strict_types=1);

namespace Aurora\Reflection\VOs\Constants;

use Aurora\Reflection\VOs\DocBlocks\DocBlockMetadata;
use JsonSerializable;

final readonly class ConstantMetadata implements JsonSerializable
{
    public function __construct(
        public string $name,
        public mixed $value,
        public bool $isPublic = true,
        public bool $isProtected = false,
        public bool $isPrivate = false,
        public bool $isFinal = false,
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
            'is_public' => $this->isPublic,
            'is_protected' => $this->isProtected,
            'is_private' => $this->isPrivate,
            'is_final' => $this->isFinal,
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
