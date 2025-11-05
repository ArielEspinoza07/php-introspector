<?php

declare(strict_types=1);

namespace Aurora\Reflection;

use Aurora\Reflection\VOs\Attributes\AttributeMetadata;
use Aurora\Reflection\VOs\Parameters\ParameterMetadata;
use Aurora\Reflection\VOs\Types\TypeMetadata;
use ReflectionParameter;

final class ParameterReader
{
    public function getMetadata(ReflectionParameter $parameter): ParameterMetadata
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
            type: $this->getType($parameter),
            attributes: $this->getAttributes($parameter),
        );
    }

    private function getType(ReflectionParameter $parameter): ?TypeMetadata
    {
        return TypeStringifier::toMetadata($parameter->getType());
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

        $reader = new AttributeReader;
        $attrsMetadata = [];

        foreach ($attributes as $attribute) {
            $attrsMetadata[] = $reader->getMetadata($attribute);
        }

        return $attrsMetadata;
    }
}
