<?php

declare(strict_types=1);

namespace Aurora\Reflection;

use Aurora\Reflection\VOs\Attributes\AttributeMetadata;
use Aurora\Reflection\VOs\Classes\ClassMetadata;
use Aurora\Reflection\VOs\DocBlocks\DocBlockMetadata;
use Aurora\Reflection\VOs\Modifiers\ClassModifier;
use Aurora\Reflection\VOs\Shared\ClassType;
use Aurora\Reflection\VOs\Shared\LinesMetadata;
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
            type: $this->getClassType($ref),
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
            docBlock: $this->getDocBlock($ref),
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
     */
    private function getClassType(ReflectionClass $ref): ClassType
    {
        return match (true) {
            $ref->isTrait() => ClassType::Trait,
            $ref->isInterface() => ClassType::Interface,
            $ref->isEnum() => ClassType::Enum,
            $ref->isAnonymous() => ClassType::Anonymous,
            default => ClassType::Class_,
        };
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

    /**
     * @param  ReflectionClass<T>  $ref
     */
    private function getDocBlock(ReflectionClass $ref): ?DocBlockMetadata
    {
        $docComment = $ref->getDocComment();
        if (! $docComment) {
            return null;
        }

        $reader = new DocBlockReader;

        return $reader->getMetadata($docComment);
    }
}
