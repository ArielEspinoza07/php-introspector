<?php

declare(strict_types=1);

namespace Aurora\Reflection\VOs;

use Aurora\Reflection\VOs\Classes\ClassMetadata;
use Aurora\Reflection\VOs\Constants\ConstantMetadata;
use Aurora\Reflection\VOs\Constructors\ConstructorMetadata;
use Aurora\Reflection\VOs\Methods\MethodMetadata;
use Aurora\Reflection\VOs\Properties\PropertyMetadata;
use JsonSerializable;

final readonly class Metadata implements JsonSerializable
{
    /**
     * @param  list<PropertyMetadata>  $properties
     * @param  list<MethodMetadata>  $methods
     * @param  list<ConstantMetadata>  $constants
     */
    public function __construct(
        public ClassMetadata $class,
        public ?ConstructorMetadata $constructor = null,
        public array $properties = [],
        public array $methods = [],
        public array $constants = [],
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'class' => $this->class->toArray(),
            'constructor' => $this->constructor?->toArray(),
            'properties' => $this->properties,
            'methods' => $this->methods,
            'constants' => $this->constants,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
