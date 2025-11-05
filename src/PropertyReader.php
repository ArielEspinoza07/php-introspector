<?php

declare(strict_types=1);

namespace Aurora\Reflection;

use Aurora\Reflection\Enums\Visibility;
use Aurora\Reflection\VOs\Attributes\AttributeMetadata;
use Aurora\Reflection\VOs\DocBlocks\DocBlockMetadata;
use Aurora\Reflection\VOs\Modifiers\PropertyModifier;
use Aurora\Reflection\VOs\Properties\PropertyMetadata;
use Aurora\Reflection\VOs\Shared\DeclaringSource;
use Aurora\Reflection\VOs\Types\TypeMetadata;
use ReflectionClass;
use ReflectionException;
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
                    visibility: $this->getVisibility($property),
                    isPromoted: $property->isPromoted(),
                    isDefault: $property->isDefault(),
                    isStatic: $property->isStatic(),
                    isReadonly: $property->isReadonly(),
                ),
                hasDefaultValue: $property->hasDefaultValue(),
                defaultValue: $property->getDefaultValue(),
                declaringSource: $this->getDeclaringSource($property, $ref),
                docBlock: $this->getDocBlock($property),
                type: $this->getType($property, $ref),
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

    /**
     * @param  ReflectionClass<T>  $classRef
     */
    private function getType(ReflectionProperty $reflectionProperty, ReflectionClass $classRef): ?TypeMetadata
    {
        $reader = new TypeReader;

        return $reader->getMetadata($reflectionProperty->getType(), $classRef);
    }

    private function getVisibility(ReflectionProperty $reflectionProperty): Visibility
    {
        if ($reflectionProperty->isPrivate()) {
            return Visibility::Private;
        }
        if ($reflectionProperty->isProtected()) {
            return Visibility::Protected;
        }

        return Visibility::Public;
    }

    /**
     * @param  ReflectionClass<T>  $classRef
     *
     * @throws ReflectionException
     */
    private function getDeclaringSource(ReflectionProperty $reflectionProperty, ReflectionClass $classRef): DeclaringSource
    {
        $reader = new DeclaringSourceReader;

        return $reader->fromProperty($reflectionProperty, $classRef);
    }
}
