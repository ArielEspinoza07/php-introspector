<?php

declare(strict_types=1);

namespace Aurora\Reflection;

use Aurora\Reflection\VOs\AttributeMetadata;
use ReflectionAttribute;

final class AttributeReader
{
    public function getMetadata(ReflectionAttribute $attribute): AttributeMetadata
    {
        return new AttributeMetadata(
            name: $attribute->getName(),
            arguments: $attribute->getArguments(),
        );
    }
}
