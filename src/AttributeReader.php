<?php

declare(strict_types=1);

namespace Aurora\Reflection;

use Aurora\Reflection\VOs\Attributes\AttributeMetadata;
use ReflectionAttribute;

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
            arguments: $attribute->getArguments(),
        );
    }
}
