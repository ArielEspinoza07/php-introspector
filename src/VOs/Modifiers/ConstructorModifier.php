<?php

declare(strict_types=1);

namespace Aurora\Reflection\VOs\Modifiers;

use Aurora\Reflection\Enums\Visibility;
use JsonSerializable;

final readonly class ConstructorModifier implements JsonSerializable
{
    public function __construct(
        public Visibility $visibility,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'visibility' => $this->visibility->value,
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
