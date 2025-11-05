<?php

declare(strict_types=1);

namespace Aurora\Reflection;

use Aurora\Reflection\Enums\Visibility;
use Aurora\Reflection\VOs\Attributes\AttributeMetadata;
use Aurora\Reflection\VOs\DocBlocks\DocBlockMetadata;
use Aurora\Reflection\VOs\Methods\MethodMetadata;
use Aurora\Reflection\VOs\Modifiers\MethodModifier;
use Aurora\Reflection\VOs\Parameters\ParameterMetadata;
use Aurora\Reflection\VOs\Shared\LinesMetadata;
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
            $lines = null;

            if ($method->getStartLine() !== false) {
                $lines = new LinesMetadata(
                    start: $method->getStartLine(),
                    end: $method->getEndLine(),
                );
            }

            $visibility = match (true) {
                $method->isPrivate() => Visibility::Private,
                $method->isPublic() => Visibility::Public,
                $method->isProtected() => Visibility::Protected,
            };

            $methsMetadata[] = new MethodMetadata(
                name: $method->getName(),
                modifier: new MethodModifier(
                    isAbstract: $method->isAbstract(),
                    isFinal: $method->isFinal(),
                    isStatic: $method->isStatic(),
                    visibility: $visibility,
                ),
                lines: $lines,
                docBlock: $this->getDocBlock($method),
                returnType: TypeReader::toMetadata($method->getReturnType()),
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

    private function getDocBlock(ReflectionMethod $ref): ?DocBlockMetadata
    {
        $docComment = $ref->getDocComment();
        if (! $docComment) {
            return null;
        }

        $reader = new DocBlockReader;

        return $reader->getMetadata($docComment);
    }
}
