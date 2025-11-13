<?php

declare(strict_types=1);

namespace Introspector;

use Introspector\Exceptions\ReflectionReadException;
use Introspector\VOs\Classes\ClassMetadata;
use Introspector\VOs\Constants\ConstantMetadata;
use Introspector\VOs\Constructors\ConstructorMetadata;
use Introspector\VOs\Metadata;
use Introspector\VOs\Methods\MethodMetadata;
use Introspector\VOs\Properties\PropertyMetadata;
use ReflectionClass;
use ReflectionException;

/**
 * @template T of object
 */
final class Reader
{
    /**
     * @param  class-string  $fqcn
     *
     * @throws ReflectionException
     */
    public function read(string $fqcn): Metadata
    {
        try {
            /** @var ReflectionClass<T> $ref */
            $ref = new ReflectionClass($fqcn);

            return new Metadata(
                class: $this->getClassMetadata($ref),
                constructor: $this->getConstructorMetadata($ref),
                properties: $this->getPropertiesMetadata($ref),
                methods: $this->getMethodsMetadata($ref),
                constants: $this->getConstantsMetadata($ref),
            );
        } catch (ReflectionException $e) {
            throw new ReflectionReadException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @param  ReflectionClass<T>  $ref
     */
    private function getClassMetadata(ReflectionClass $ref): ClassMetadata
    {
        $reader = new ClassReader();

        return $reader->getMetadata($ref);
    }

    /**
     * @param  ReflectionClass<T>  $ref
     */
    private function getConstructorMetadata(ReflectionClass $ref): ?ConstructorMetadata
    {
        $reader = new ConstructorReader();

        return $reader->getMetadata($ref);
    }

    /**
     * @param  ReflectionClass<T>  $ref
     * @return list<PropertyMetadata>
     */
    private function getPropertiesMetadata(ReflectionClass $ref): array
    {
        $reader = new PropertyReader();

        return $reader->getMetadata($ref);
    }

    /**
     * @param  ReflectionClass<T>  $ref
     * @return list<MethodMetadata>
     */
    private function getMethodsMetadata(ReflectionClass $ref): array
    {
        $reader = new MethodReader();

        return $reader->getMetadata($ref);
    }

    /**
     * @param  ReflectionClass<T>  $ref
     * @return list<ConstantMetadata>
     */
    private function getConstantsMetadata(ReflectionClass $ref): array
    {
        $reader = new ConstantReader();

        return $reader->getMetadata($ref);
    }
}
