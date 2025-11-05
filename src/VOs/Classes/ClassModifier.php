<?php

declare(strict_types=1);

namespace Aurora\Reflection\VOs\Classes;

use JsonSerializable;

final readonly class ClassModifier implements JsonSerializable
{
    public function __construct(
        public bool $isAbstract,
        public bool $isFinal,
        public bool $isReadonly,
        public bool $isInternal,
        public bool $isAnonymous,
        public bool $isInstantiable,
    ) {}

    /**
     * @return array<string, bool>
     */
    public function toArray(): array
    {
        return [
            'is_abstract' => $this->isAbstract,
            'is_final' => $this->isFinal,
            'is_readonly' => $this->isReadonly,
            'is_internal' => $this->isInternal,
            'is_anonymous' => $this->isAnonymous,
            'is_instantiable' => $this->isInstantiable,
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
