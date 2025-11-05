<?php

declare(strict_types=1);

namespace Aurora\Reflection;

use Aurora\Reflection\VOs\Attributes\AttributeMetadata;
use Aurora\Reflection\VOs\Methods\MethodMetadata;
use Aurora\Reflection\VOs\Methods\MethodModifier;
use Aurora\Reflection\VOs\Parameters\ParameterMetadata;
use ReflectionClass;
use ReflectionMethod;

/**
 * @template T of object
 */
final class MethodReader
{
    /**
     * @param  ReflectionClass<T>  $ref
     * @return list<MethodMetadata>
     */
    public function getMetadata(ReflectionClass $ref): array
    {
        $methods = $ref->getMethods();
        if (count($methods) === 0) {
            return [];
        }

        $methsMetadata = [];

        foreach ($methods as $method) {
            if ($method->isConstructor()) {
                continue;
            }

            $methsMetadata[] = new MethodMetadata(
                name: $method->getName(),
                modifier: new MethodModifier(
                    isAbstract: $method->isAbstract(),
                    isFinal: $method->isFinal(),
                    isStatic: $method->isStatic(),
                    isPrivate: $method->isPrivate(),
                    isProtected: $method->isProtected(),
                    isPublic: $method->isPublic(),
                ),
                returnType: TypeStringifier::toString($method->getReturnType()),
                parameters: $this->getParameters($method),
                attributes: $this->getAttributes($method),
            );
        }

        return $methsMetadata;
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
}
