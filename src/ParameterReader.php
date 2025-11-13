<?php

declare(strict_types=1);

namespace Introspector;

use Introspector\VOs\Attributes\AttributeMetadata;
use Introspector\VOs\Parameters\ParameterMetadata;
use Introspector\VOs\Types\TypeMetadata;
use ReflectionClass;
use ReflectionParameter;

/**
 * @template T of object
 */
final class ParameterReader
{
    /**
     * @param  ReflectionClass<T>|null  $context
     */
    public function getMetadata(ReflectionParameter $parameter, ?ReflectionClass $context = null): ParameterMetadata
    {
        return new ParameterMetadata(
            name: $parameter->getName(),
            isVariadic: $parameter->isVariadic(),
            isOptional: $parameter->isOptional(),
            isPromoted: $parameter->isPromoted(),
            position: $parameter->getPosition(),
            isPassedByReference: $parameter->isPassedByReference(),
            isPassedByValue: $parameter->canBePassedByValue(),
            allowsNull: $parameter->allowsNull(),
            hasDefaultValue: $parameter->isDefaultValueAvailable(),
            defaultValue: $parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : null,
            type: $this->getType($parameter, $context),
            attributes: $this->getAttributes($parameter),
        );
    }

    /**
     * @param  ReflectionClass<T>|null  $context
     */
    private function getType(ReflectionParameter $parameter, ?ReflectionClass $context = null): ?TypeMetadata
    {
        $reader = new TypeReader();

        return $reader->getMetadata($parameter->getType(), $context);
    }

    /**
     * @return list<AttributeMetadata>
     */
    private function getAttributes(ReflectionParameter $parameter): array
    {
        $attributes = $parameter->getAttributes();

        if (count($attributes) === 0) {
            return [];
        }

        $reader = new AttributeReader();
        $attrsMetadata = [];

        foreach ($attributes as $attribute) {
            $attrsMetadata[] = $reader->getMetadata($attribute);
        }

        return $attrsMetadata;
    }
}
