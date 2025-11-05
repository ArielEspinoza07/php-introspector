<?php

declare(strict_types=1);

namespace Aurora\Reflection\VOs\Modifiers;

use JsonSerializable;

final readonly class ConstructorModifier implements JsonSerializable
{
    public function __construct(
        public bool $isPublic,
        public bool $isProtected,
        public bool $isPrivate,
    ) {}

    /**
     * @return array<string, bool>
     */
    public function toArray(): array
    {
        return [
            'is_public' => $this->isPublic,
            'is_protected' => $this->isProtected,
            'is_private' => $this->isPrivate,
        ];
    }

    /**
     * @return array<string, bool>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
