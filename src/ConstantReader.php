<?php

declare(strict_types=1);

namespace Aurora\Reflection;

use Aurora\Reflection\VOs\Constants\ConstantMetadata;
use Aurora\Reflection\VOs\DocBlocks\DocBlockMetadata;
use ReflectionClass;
use ReflectionClassConstant;

/**
 * @template T of object
 */
final class ConstantReader
{
    /**
     * @param  ReflectionClass<T>  $ref
     * @return list<ConstantMetadata>
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
                isPublic: $constant->isPublic(),
                isProtected: $constant->isProtected(),
                isPrivate: $constant->isPrivate(),
                isFinal: $this->isFinal($constant),
                docBlock: $this->getDocBlock($constant),
            );
        }

        return $constantsMetadata;
    }

    private function isFinal(ReflectionClassConstant $constant): bool
    {
        // isFinal() is available since PHP 8.1
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
}
