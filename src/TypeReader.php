<?php

declare(strict_types=1);

namespace Aurora\Reflection;

use Aurora\Reflection\VOs\Types\TypeMetadata;
use ReflectionClass;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionType;
use ReflectionUnionType;

/**
 * @template T of object
 */
final class TypeReader
{
    /**
     * @param  ReflectionClass<T>|null  $context
     */
    public function getMetadata(?ReflectionType $type, ?ReflectionClass $context = null): ?TypeMetadata
    {
        if ($type === null) {
            return null;
        }

        $nullable = $type->allowsNull();

        if ($type instanceof ReflectionNamedType) {
            $name = $type->getName();
            $isSpecial = $this->isSpecialType($name);
            $resolvedName = $isSpecial && $context !== null
                ? $this->resolveSpecialType($name, $context)
                : null;

            if ($resolvedName === null) {
                $resolvedName = $name;
            }

            return new TypeMetadata(
                name: $name,
                resolvedName: $resolvedName,
                isBuiltin: $type->isBuiltin(),
                isNullable: $nullable && $name !== 'mixed',
                isUnion: false,
                isIntersection: false,
                isSpecial: $isSpecial,
            );
        }

        if ($type instanceof ReflectionUnionType) {
            $unionTypes = array_map(
                fn (ReflectionType $t) => $this->getMetadata($t, $context),
                $type->getTypes()
            );

            // Filter out nulls
            $unionTypes = array_filter($unionTypes, fn ($t) => $t !== null);

            return new TypeMetadata(
                name: $this->toString($type),
                isBuiltin: false,
                isNullable: $nullable,
                isUnion: true,
                isIntersection: false,
                unionTypes: array_values($unionTypes),
            );
        }

        if ($type instanceof ReflectionIntersectionType) {
            $intersectionTypes = array_map(
                fn (ReflectionType $t) => $this->getMetadata($t, $context),
                $type->getTypes()
            );

            // Filter out nulls
            $intersectionTypes = array_filter($intersectionTypes, fn ($t) => $t !== null);

            return new TypeMetadata(
                name: $this->toString($type),
                isBuiltin: false,
                isNullable: false,
                isUnion: false,
                isIntersection: true,
                intersectionTypes: array_values($intersectionTypes),
            );
        }

        return null;
    }

    /**
     * Check if a type name is a special PHP type (self, parent, static)
     */
    private function isSpecialType(string $typeName): bool
    {
        return in_array($typeName, ['self', 'parent', 'static'], true);
    }

    /**
     * Resolve special types (self, parent, static) to their actual class names
     *
     * @param  ReflectionClass<T>  $context
     */
    private function resolveSpecialType(string $typeName, ReflectionClass $context): ?string
    {
        return match ($typeName) {
            'self', 'static' => $context->getName(),
            'parent' => $context->getParentClass() !== false ? $context->getParentClass()->getName() : null,
            default => null,
        };
    }

    private function toString(?ReflectionType $type): ?string
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
}
