<?php

declare(strict_types=1);

namespace Aurora\Reflection;

use Aurora\Reflection\Enums\ClassType;
use Aurora\Reflection\Enums\SourceType;
use Aurora\Reflection\VOs\Attributes\AttributeMetadata;
use Aurora\Reflection\VOs\Classes\ClassMetadata;
use Aurora\Reflection\VOs\DocBlocks\DocBlockMetadata;
use Aurora\Reflection\VOs\Modifiers\ClassModifier;
use Aurora\Reflection\VOs\Shared\DeclaringSource;
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
        $file = 'Not Found';
        if ($ref->getFileName() !== false) {
            $file = $ref->getFileName();
        }

        return new ClassMetadata(
            name: $ref->getName(),
            shortName: $ref->getShortName(),
            nameSpace: $ref->getNamespaceName(),
            file: $file,
            type: $this->getClassType($ref),
            modifier: new ClassModifier(
                isAbstract: $ref->isAbstract(),
                isFinal: $ref->isFinal(),
                isReadonly: $ref->isReadonly(),
                isInternal: $ref->isInternal(),
                isAnonymous: $ref->isAnonymous(),
                isInstantiable: $ref->isInstantiable(),
            ),
            lines: $this->getLines($ref),
            docBlock: $this->getDocBlock($ref),
            extends: $this->getParent($ref),
            implements: $this->getImplements($ref),
            traits: $this->getTraits($ref),
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
            $ref->isTrait() => ClassType::Trait_,
            $ref->isInterface() => ClassType::Interface_,
            $ref->isEnum() => ClassType::Enum_,
            $ref->isAnonymous() => ClassType::Anonymous_,
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

        $reader = new AttributeReader();
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

        $reader = new DocBlockReader();

        return $reader->getMetadata($docComment);
    }

    /**
     * @param  ReflectionClass<T>  $ref
     */
    private function getLines(ReflectionClass $ref): ?LinesMetadata
    {
        if ($ref->getStartLine() !== false && $ref->getEndLine() !== false) {
            return new LinesMetadata(
                start: $ref->getStartLine(),
                end: $ref->getEndLine(),
            );
        }

        return null;
    }

    /**
     * @param  ReflectionClass<T>  $ref
     * @return list<DeclaringSource>
     */
    private function getImplements(ReflectionClass $ref): array
    {
        $implements = [];
        foreach ($ref->getInterfaces() as $interface) {
            $implements[] = new DeclaringSource(
                type: SourceType::Interface_,
                className: $interface->getName(),
                shortName: $interface->getShortName(),
                namespace: $interface->getNamespaceName(),
            );
        }

        return $implements;
    }

    /**
     * @param  ReflectionClass<T>  $ref
     * @return list<DeclaringSource>
     */
    private function getTraits(ReflectionClass $ref): array
    {
        $traits = [];
        foreach ($ref->getTraits() as $trait) {
            $traits[] = new DeclaringSource(
                type: SourceType::Trait_,
                className: $trait->getName(),
                shortName: $trait->getShortName(),
                namespace: $trait->getNamespaceName(),
            );
        }

        return $traits;
    }
}
