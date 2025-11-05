<?php

declare(strict_types=1);

namespace Aurora\Reflection\VOs\Modifiers;

use Aurora\Reflection\Enums\Visibility;
use JsonSerializable;

final readonly class PropertyModifier implements JsonSerializable
{
    public function __construct(
        public Visibility $visibility,
        public bool $isPromoted,
        public bool $isDefault,
        public bool $isStatic,
        public bool $isReadonly,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'visibility' => $this->visibility->value,
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
