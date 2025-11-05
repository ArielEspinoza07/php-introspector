<?php

declare(strict_types=1);

namespace Aurora\Reflection;

use Aurora\Reflection\VOs\Types\TypeMetadata;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionType;
use ReflectionUnionType;

final class TypeReader
{
    public static function toString(?ReflectionType $type): ?string
    {
        if ($type === null) {
            return null;
        }

        $nullable = $type->allowsNull();

        if ($type instanceof ReflectionNamedType) {
            $name = $type->getName();

            return $nullable && $name !== 'mixed' ? "?{$name}" : $name;
        }

        if ($type instanceof ReflectionUnionType) {
            $parts = array_map(
                fn (ReflectionType $t) => ltrim((string) self::toString($t), '?'),
                $type->getTypes()
            );

            return implode('|', $parts);
        }

        if ($type instanceof ReflectionIntersectionType) {
            $parts = array_map(
                fn (ReflectionType $t) => (string) self::toString($t),
                $type->getTypes()
            );

            return implode('&', $parts);
        }

        return null;
    }

    public static function isBuiltin(ReflectionType $type): bool
    {
        if ($type instanceof ReflectionNamedType) {
            return $type->isBuiltin();
        }

        return false;
    }

    public static function toMetadata(?ReflectionType $type): ?TypeMetadata
    {
        if ($type === null) {
            return null;
        }

        $nullable = $type->allowsNull();

        if ($type instanceof ReflectionNamedType) {
            $name = $type->getName();

            return new TypeMetadata(
                name: $name,
                isBuiltin: $type->isBuiltin(),
                isNullable: $nullable && $name !== 'mixed',
                isUnion: false,
                isIntersection: false,
            );
        }

        if ($type instanceof ReflectionUnionType) {
            $unionTypes = array_map(
                fn (ReflectionType $t) => self::toMetadata($t),
                $type->getTypes()
            );

            // Filter out nulls
            $unionTypes = array_filter($unionTypes, fn ($t) => $t !== null);

            return new TypeMetadata(
                name: self::toString($type),
                isBuiltin: false,
                isNullable: $nullable,
                isUnion: true,
                isIntersection: false,
                unionTypes: array_values($unionTypes),
            );
        }

        if ($type instanceof ReflectionIntersectionType) {
            $intersectionTypes = array_map(
                fn (ReflectionType $t) => self::toMetadata($t),
                $type->getTypes()
            );

            // Filter out nulls
            $intersectionTypes = array_filter($intersectionTypes, fn ($t) => $t !== null);

            return new TypeMetadata(
                name: self::toString($type),
                isBuiltin: false,
                isNullable: false,
                isUnion: false,
                isIntersection: true,
                intersectionTypes: array_values($intersectionTypes),
            );
        }

        return null;
    }
}
