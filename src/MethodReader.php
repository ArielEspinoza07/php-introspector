<?php

declare(strict_types=1);

namespace Aurora\Reflection;

use Aurora\Reflection\Enums\Visibility;
use Aurora\Reflection\VOs\Attributes\AttributeMetadata;
use Aurora\Reflection\VOs\DocBlocks\DocBlockMetadata;
use Aurora\Reflection\VOs\Methods\MethodMetadata;
use Aurora\Reflection\VOs\Modifiers\MethodModifier;
use Aurora\Reflection\VOs\Parameters\ParameterMetadata;
use Aurora\Reflection\VOs\Shared\DeclaringSource;
use Aurora\Reflection\VOs\Shared\LinesMetadata;
use Aurora\Reflection\VOs\Types\TypeMetadata;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

/**
 * @template T of object
 */
final class MethodReader
{
    /**
     * @param  ReflectionClass<T>  $ref
     * @return list<MethodMetadata>
     *
     * @throws ReflectionException
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
                    visibility: $this->getVisibility($method),
                ),
                declaringSource: $this->getDeclaringSource($method, $ref),
                lines: $this->getLines($method),
                docBlock: $this->getDocBlock($method),
                returnType: $this->getType($method, $ref),
                parameters: $this->getParameters($method, $ref),
                attributes: $this->getAttributes($method),
            );
        }

        return $methsMetadata;
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

    private function getDocBlock(ReflectionMethod $ref): ?DocBlockMetadata
    {
        $docComment = $ref->getDocComment();
        if (! $docComment) {
            return null;
        }

        $reader = new DocBlockReader();

        return $reader->getMetadata($docComment);
    }

    /**
     * @param  ReflectionClass<T>  $classRef
     */
    private function getType(ReflectionMethod $ref, ReflectionClass $classRef): ?TypeMetadata
    {
        $reader = new TypeReader();

        return $reader->getMetadata($ref->getReturnType(), $classRef);
    }

    private function getLines(ReflectionMethod $ref): ?LinesMetadata
    {
        if ($ref->getStartLine() !== false && $ref->getEndLine() !== false) {
            return new LinesMetadata(
                start: $ref->getStartLine(),
                end: $ref->getEndLine(),
            );
        }

        return null;
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
     * @param  ReflectionClass<T>  $classRef
     *
     * @throws ReflectionException
     */
    private function getDeclaringSource(ReflectionMethod $ref, ReflectionClass $classRef): DeclaringSource
    {
        $reader = new DeclaringSourceReader();

        return $reader->fromMethod($ref, $classRef);
    }
}
