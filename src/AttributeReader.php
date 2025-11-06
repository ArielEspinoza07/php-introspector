<?php

declare(strict_types=1);

namespace Aurora\Reflection;

use Aurora\Reflection\VOs\Attributes\AttributeMetadata;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;

/**
 * @template T of object
 */
final class AttributeReader
{
    /**
     * @param  ReflectionAttribute<T>  $attribute
     */
    public function getMetadata(ReflectionAttribute $attribute): AttributeMetadata
    {
        return new AttributeMetadata(
            name: $attribute->getName(),
            fqcn: $this->getFQCN($attribute),
            namespace: $this->getNamespace($attribute),
            arguments: $attribute->getArguments(),
        );
    }

    /**
     * @param  ReflectionAttribute<T>  $attribute
     *
     * @throws ReflectionException
     */
    private function getFQCN(ReflectionAttribute $attribute): string
    {
        $ref = new ReflectionClass($attribute->getName());

        if (empty($ref->getNamespaceName())) {
            return $ref->getNamespaceName().'\\'.$ref->getName();
        }

        return $ref->getName();
    }

    /**
     * @param  ReflectionAttribute<T>  $attribute
     *
     * @throws ReflectionException
     */
    private function getNamespace(ReflectionAttribute $attribute): string
    {
        $ref = new ReflectionClass($attribute->getName());

        return $ref->getNamespaceName();
    }
}
