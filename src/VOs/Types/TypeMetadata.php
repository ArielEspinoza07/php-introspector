<?php

declare(strict_types=1);

namespace Aurora\Reflection\VOs\Types;

use JsonSerializable;

final readonly class TypeMetadata implements JsonSerializable
{
    /**
     * @param  list<TypeMetadata>  $unionTypes
     * @param  list<TypeMetadata>  $intersectionTypes
     */
    public function __construct(
        public ?string $name = null,
        public bool $isBuiltin = false,
        public bool $isNullable = false,
        public bool $isUnion = false,
        public bool $isIntersection = false,
        public array $unionTypes = [],
        public array $intersectionTypes = [],
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'is_builtin' => $this->isBuiltin,
            'is_nullable' => $this->isNullable,
            'is_union' => $this->isUnion,
            'is_intersection' => $this->isIntersection,
            'union_types' => array_map(fn (TypeMetadata $type) => $type->toArray(), $this->unionTypes),
            'intersection_types' => array_map(fn (TypeMetadata $type) => $type->toArray(), $this->intersectionTypes),
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
