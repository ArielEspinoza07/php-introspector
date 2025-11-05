<?php

declare(strict_types=1);

namespace Aurora\Reflection\VOs\Methods;

use JsonSerializable;

final readonly class MethodModifier implements JsonSerializable
{
    public function __construct(
        public bool $isAbstract,
        public bool $isFinal,
        public bool $isStatic,
        public bool $isPrivate,
        public bool $isProtected,
        public bool $isPublic,
    ) {}

    /**
     * @return array<string, bool>
     */
    public function toArray(): array
    {
        return [
            'is_abstract' => $this->isAbstract,
            'is_final' => $this->isFinal,
            'is_static' => $this->isStatic,
            'is_private' => $this->isPrivate,
            'is_protected' => $this->isProtected,
            'is_public' => $this->isPublic,
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