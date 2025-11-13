<?php

declare(strict_types=1);

namespace Introspector\VOs;

use Introspector\VOs\Classes\ClassMetadata;
use Introspector\VOs\Constants\ConstantMetadata;
use Introspector\VOs\Constructors\ConstructorMetadata;
use Introspector\VOs\Methods\MethodMetadata;
use Introspector\VOs\Properties\PropertyMetadata;
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
