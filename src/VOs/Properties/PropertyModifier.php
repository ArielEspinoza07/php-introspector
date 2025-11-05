<?php

declare(strict_types=1);

namespace Aurora\Reflection\VOs\Properties;

use JsonSerializable;

final readonly class PropertyModifier implements JsonSerializable
{
    public function __construct(
        public bool $isPrivate,
        public bool $isProtected,
        public bool $isPublic,
        public bool $isPromoted,
        public bool $isDefault,
        public bool $isStatic,
        public bool $isReadonly,
    ) {}

    /**
     * @return array<string, bool>
     */
    public function toArray(): array
    {
        return [
            'is_private' => $this->isPrivate,
            'is_protected' => $this->isProtected,
            'is_public' => $this->isPublic,
            'is_promoted' => $this->isPromoted,
            'is_default' => $this->isDefault,
            'is_static' => $this->isStatic,
            'is_readonly' => $this->isReadonly,
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
