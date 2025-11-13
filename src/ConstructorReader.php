<?php

declare(strict_types=1);

namespace Introspector;

use Introspector\Enums\Visibility;
use Introspector\VOs\Attributes\AttributeMetadata;
use Introspector\VOs\Constructors\ConstructorMetadata;
use Introspector\VOs\DocBlocks\DocBlockMetadata;
use Introspector\VOs\Modifiers\ConstructorModifier;
use Introspector\VOs\Parameters\ParameterMetadata;
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
                visibility: $this->getVisibility($constr)
            ),
            docBlock: $this->getDocBlock($constr),
            parameters: $this->getParameters($constr, $ref),
            attributes: $this->getAttributes($constr),
        );
    }

    private function getVisibility(ReflectionMethod $ref): Visibility
    {
        if ($ref->isPrivate()) {
            return Visibility::Private;
        }
        if ($ref->isProtected()) {
            return Visibility::Protected;
        }

        return Visibility::Public;
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

        $reader = new AttributeReader();
        $attrsMetadata = [];

        foreach ($attributes as $attribute) {
            $attrsMetadata[] = $reader->getMetadata($attribute);
        }

        return $attrsMetadata;
    }

    /**
     * @param  ReflectionClass<T>  $classRef
     * @return list<ParameterMetadata>
     */
    private function getParameters(ReflectionMethod $ref, ReflectionClass $classRef): array
    {
        $parameters = $ref->getParameters();

        if (count($parameters) === 0) {
            return [];
        }

        $reader = new ParameterReader();
        $parmsMetadata = [];

        foreach ($parameters as $parameter) {
            $parmsMetadata[] = $reader->getMetadata($parameter, $classRef);
        }

        return $parmsMetadata;
    }

    private function getDocBlock(ReflectionMethod $ref): ?DocBlockMetadata
    {
        $docComment = $ref->getDocComment();
        if (! $docComment) {
            return null;
        }

        $reader = new DocBlockReader();

        return $reader->getMetadata($docComment);
    }
}
