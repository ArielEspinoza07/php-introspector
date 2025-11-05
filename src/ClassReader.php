<?php

declare(strict_types=1);

namespace Aurora\Reflection;

use Aurora\Reflection\VOs\Attributes\AttributeMetadata;
use Aurora\Reflection\VOs\Classes\ClassMetadata;
use Aurora\Reflection\VOs\Classes\ClassModifier;
use Aurora\Reflection\VOs\Classes\LinesMetadata;
use ReflectionClass;

/**
 * @template T of object
 */
final class ClassReader
{
    /**
     * @param  ReflectionClass<T>  $ref
     */
    public function getMetadata(ReflectionClass $ref): ClassMetadata
    {
        return new ClassMetadata(
            name: $ref->getName(),
            shortName: $ref->getShortName(),
            nameSpace: $ref->getNamespaceName(),
            file: $ref->getFileName(),
            lines: new LinesMetadata(
                start: $ref->getStartLine(),
                end: $ref->getEndLine(),
            ),
            modifier: new ClassModifier(
                isAbstract: $ref->isAbstract(),
                isFinal: $ref->isFinal(),
                isReadonly: $ref->isReadonly(),
                isInternal: $ref->isInternal(),
                isAnonymous: $ref->isAnonymous(),
                isInstantiable: $ref->isInstantiable(),
            ),
            extends: $this->getParent($ref),
            implements: $ref->getInterfaceNames(),
            traits: $ref->getTraitNames(),
            attributes: $this->getAttributes($ref),
        );
    }

    /**
     * @param  ReflectionClass<T>  $ref
     */
    private function getParent(ReflectionClass $ref): ?string
    {
        $parent = $ref->getParentClass();
        if (! $parent) {
            return null;
        }

        return $parent->getName();
    }

    /**
     * @param  ReflectionClass<T>  $ref
     * @return list<AttributeMetadata>
     */
    private function getAttributes(ReflectionClass $ref): array
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
