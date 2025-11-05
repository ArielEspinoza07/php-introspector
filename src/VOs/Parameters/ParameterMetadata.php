<?php

declare(strict_types=1);

namespace Aurora\Reflection\VOs\Parameters;

use Aurora\Reflection\VOs\AttributeMetadata;
use JsonSerializable;

final readonly class ParameterMetadata implements JsonSerializable
{
    /**
     * @param  list<AttributeMetadata>  $attributes
     */
    public function __construct(
        public string $name,
        public bool $isVariadic,
        public bool $isOptional,
        public bool $isPromoted,
        public int $position,
        public bool $isPassedByReference,
        public bool $isPassedByValue,
        public bool $allowsNull,
        public bool $hasDefaultValue,
        public mixed $defaultValue,
        public ?string $type = null,
        public array $attributes = [],
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'is_variadic' => $this->isVariadic,
            'is_optional' => $this->isOptional,
            'is_promoted' => $this->isPromoted,
            'position' => $this->position,
            'is_passed_by_reference' => $this->isPassedByReference,
            'is_passed_by_value' => $this->isPassedByValue,
            'allows_null' => $this->allowsNull,
            'has_default_value' => $this->hasDefaultValue,
            'default_value' => $this->defaultValue,
            'type' => $this->type,
            'attributes' => $this->attributes,
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
