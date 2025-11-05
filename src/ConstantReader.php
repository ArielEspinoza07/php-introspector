<?php

declare(strict_types=1);

namespace Aurora\Reflection;

use Aurora\Reflection\Enums\Visibility;
use Aurora\Reflection\VOs\Constants\ConstantMetadata;
use Aurora\Reflection\VOs\DocBlocks\DocBlockMetadata;
use Aurora\Reflection\VOs\Shared\DeclaringSource;
use Aurora\Reflection\VOs\Types\TypeMetadata;
use ReflectionClass;
use ReflectionClassConstant;
use ReflectionException;

/**
 * @template T of object
 */
final class ConstantReader
{
    /**
     * @param  ReflectionClass<T>  $ref
     * @return list<ConstantMetadata>
     *
     * @throws ReflectionException
     */
    public function getMetadata(ReflectionClass $ref): array
    {
        $constants = $ref->getReflectionConstants();

        if (count($constants) === 0) {
            return [];
        }

        $constantsMetadata = [];

        foreach ($constants as $constant) {
            $constantsMetadata[] = new ConstantMetadata(
                name: $constant->getName(),
                value: $constant->getValue(),
                visibility: $this->getVisibility($constant),
                declaringSource: $this->getDeclaringSource($constant, $ref),
                isFinal: $this->isFinal($constant),
                type: $this->getType($constant, $ref),
                docBlock: $this->getDocBlock($constant),
            );
        }

        return $constantsMetadata;
    }

    private function isFinal(ReflectionClassConstant $constant): bool
    {
        /** @phpstan-ignore-next-line */
        if (method_exists($constant, 'isFinal')) {
            return $constant->isFinal();
        }

        return false;
    }

    private function getDocBlock(ReflectionClassConstant $constant): ?DocBlockMetadata
    {
        $docComment = $constant->getDocComment();
        if (! $docComment) {
            return null;
        }

        $reader = new DocBlockReader;

        return $reader->getMetadata($docComment);
    }

    /**
     * @param  ReflectionClass<T>|null  $context
     */
    private function getType(ReflectionClassConstant $constant, ?ReflectionClass $context = null): ?TypeMetadata
    {
        /** @phpstan-ignore-next-line */
        if (! method_exists($constant, 'getType')) {
            return null;
        }

        $reader = new TypeReader;

        return $reader->getMetadata($constant->getType(), $context);
    }

    private function getVisibility(ReflectionClassConstant $constant): Visibility
    {
        if ($constant->isPrivate()) {
            return Visibility::Private;
        }
        if ($constant->isProtected()) {
            return Visibility::Protected;
        }

        return Visibility::Public;
    }

    /**
     * @param  ReflectionClass<T>  $classRef
     *
     * @throws ReflectionException
     */
    private function getDeclaringSource(ReflectionClassConstant $constant, ReflectionClass $classRef): DeclaringSource
    {
        $reader = new DeclaringSourceReader;

        return $reader->fromConstant($constant, $classRef);
    }
}
