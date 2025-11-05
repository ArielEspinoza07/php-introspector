<?php

declare(strict_types=1);

namespace Aurora\Reflection;

use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionType;
use ReflectionUnionType;

final class TypeStringifier
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
}
