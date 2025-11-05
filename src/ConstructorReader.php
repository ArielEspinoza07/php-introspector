<?php

declare(strict_types=1);

namespace Aurora\Reflection;

use Aurora\Reflection\VOs\Attributes\AttributeMetadata;
use Aurora\Reflection\VOs\Constructors\ConstructorMetadata;
use Aurora\Reflection\VOs\Constructors\ConstructorModifier;
use Aurora\Reflection\VOs\Parameters\ParameterMetadata;
use ReflectionClass;
use ReflectionMethod;

/**
 * @template T of object
 */
final class ConstructorReader
{
    /**
     * @param  ReflectionClass<T>  $ref
     */
    public function getMetadata(ReflectionClass $ref): ?ConstructorMetadata
    {
        $constr = $ref->getConstructor();

        if (! $constr) {
            return null;
        }

        return new ConstructorMetadata(
            modifier: new ConstructorModifier(
                isPublic: $constr->isPublic(),
                isProtected: $constr->isProtected(),
                isPrivate: $constr->isPrivate(),
            ),
            parameters: $this->getParameters($constr),
            attributes: $this->getAttributes($constr),
        );
    }

    /**
     * @return list<AttributeMetadata>
     */
    private function getAttributes(ReflectionMethod $ref): array
    {
        $attributes = $ref->getAttributes();

        if (count($attributes) === 0) {
            return [];
        }

        $reader = new AttributeReader;
        $attrsMetadata = [];

        foreach ($attributes as $attribute) {
            $attrsMetadata[] = $reader->getMetadata($attribute);
        }

        return $attrsMetadata;
    }

    /**
     * @return list<ParameterMetadata>
     */
    private function getParameters(ReflectionMethod $ref): array
    {
        $parameters = $ref->getParameters();

        if (count($parameters) === 0) {
            return [];
        }

        $reader = new ParameterReader;
        $parmsMetadata = [];

        foreach ($parameters as $parameter) {
            $parmsMetadata[] = $reader->getMetadata($parameter);
        }

        return $parmsMetadata;
    }
}
