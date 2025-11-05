<?php

declare(strict_types=1);

namespace Aurora\Reflection;

use Aurora\Reflection\VOs\Attributes\AttributeMetadata;
use Aurora\Reflection\VOs\DocBlocks\DocBlockMetadata;
use Aurora\Reflection\VOs\Modifiers\PropertyModifier;
use Aurora\Reflection\VOs\Properties\PropertyMetadata;
use ReflectionClass;
use ReflectionProperty;

/**
 * @template T of object
 */
final class PropertyReader
{
    /**
     * @param  ReflectionClass<T>  $ref
     * @return list<PropertyMetadata>
     */
    public function getMetadata(ReflectionClass $ref): array
    {
        $properties = $ref->getProperties();
        if (count($properties) === 0) {
            return [];
        }

        $propsMetadata = [];
        foreach ($properties as $property) {
            $propsMetadata[] = new PropertyMetadata(
                name: $property->getName(),
                modifier: new PropertyModifier(
                    isPrivate: $property->isPrivate(),
                    isProtected: $property->isProtected(),
                    isPublic: $property->isPublic(),
                    isPromoted: $property->isPromoted(),
                    isDefault: $property->isDefault(),
                    isStatic: $property->isStatic(),
                    isReadonly: $property->isReadonly(),
                ),
                hasDefaultValue: $property->hasDefaultValue(),
                defaultValue: $property->getDefaultValue(),
                docBlock: $this->getDocBlock($property),
                type: TypeReader::toMetadata($property->getType()),
                attributes: $this->getAttributes($property),
            );
        }

        return $propsMetadata;
    }

    /**
     * @return list<AttributeMetadata>
     */
    private function getAttributes(ReflectionProperty $reflectionProperty): array
    {
        $attributes = $reflectionProperty->getAttributes();

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

    private function getDocBlock(ReflectionProperty $reflectionProperty): ?DocBlockMetadata
    {
        $docComment = $reflectionProperty->getDocComment();
        if (! $docComment) {
            return null;
        }

        $reader = new DocBlockReader;

        return $reader->getMetadata($docComment);
    }
}
