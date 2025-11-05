<?php

declare(strict_types=1);

namespace Aurora\Reflection\VOs\Modifiers;

use Aurora\Reflection\Enums\Visibility;
use JsonSerializable;

final readonly class MethodModifier implements JsonSerializable
{
    public function __construct(
        public bool $isAbstract,
        public bool $isFinal,
        public bool $isStatic,
        public Visibility $visibility,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'is_abstract' => $this->isAbstract,
            'is_final' => $this->isFinal,
            'is_static' => $this->isStatic,
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