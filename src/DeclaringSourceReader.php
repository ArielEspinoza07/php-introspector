<?php

declare(strict_types=1);

namespace Aurora\Reflection;

use Aurora\Reflection\Enums\SourceType;
use Aurora\Reflection\VOs\Shared\DeclaringSource;
use ReflectionClass;
use ReflectionClassConstant;
use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;

/**
 * @template T of object
 */
final class DeclaringSourceReader
{
    /**
     * Get the declaring source for a property
     *
     * @param  ReflectionClass<T>  $currentClass
     *
     * @throws ReflectionException
     */
    public function fromProperty(ReflectionProperty $property, ReflectionClass $currentClass): DeclaringSource
    {
        $declaringClass = $property->getDeclaringClass();

        return $this->createDeclaringSource($declaringClass, $currentClass);
    }

    /**
     * Get the declaring source for a method
     *
     * @param  ReflectionClass<T>  $currentClass
     *
     * @throws ReflectionException
     */
    public function fromMethod(ReflectionMethod $method, ReflectionClass $currentClass): DeclaringSource
    {
        $declaringClass = $method->getDeclaringClass();

        return $this->createDeclaringSource($declaringClass, $currentClass);
    }

    /**
     * Get the declaring source for a constant
     *
     * @param  ReflectionClass<T>  $currentClass
     *
     * @throws ReflectionException
     */
    public function fromConstant(ReflectionClassConstant $constant, ReflectionClass $currentClass): DeclaringSource
    {
        $declaringClass = $constant->getDeclaringClass();

        return $this->createDeclaringSource($declaringClass, $currentClass);
    }

    /**
     * Create a DeclaringSource VO by determining the source type
     *
     * @param  ReflectionClass<T>  $declaringClass
     * @param  ReflectionClass<T>  $currentClass
     *
     * @throws ReflectionException
     */
    private function createDeclaringSource(ReflectionClass $declaringClass, ReflectionClass $currentClass): DeclaringSource
    {
        $declaringClassName = $declaringClass->getName();
        $currentClassName = $currentClass->getName();

        // Check if it's declared in the current class
        if ($declaringClassName === $currentClassName) {
            return new DeclaringSource(
                type: SourceType::Self_,
                className: $declaringClassName,
                shortName: $declaringClass->getShortName(),
            );
        }

        // Check if it comes from a trait
        $traits = $this->getAllTraits($currentClass);
        if (in_array($declaringClassName, $traits, true)) {
            return new DeclaringSource(
                type: SourceType::Trait_,
                className: $declaringClassName,
                shortName: $declaringClass->getShortName(),
            );
        }

        // Check if it comes from an interface
        $interfaces = $currentClass->getInterfaceNames();
        if (in_array($declaringClassName, $interfaces, true)) {
            return new DeclaringSource(
                type: SourceType::Interface_,
                className: $declaringClassName,
                shortName: $declaringClass->getShortName(),
            );
        }

        // Otherwise, it's from a parent class
        return new DeclaringSource(
            type: SourceType::Parent_,
            className: $declaringClassName,
            shortName: $declaringClass->getShortName(),
        );
    }

    /**
     * Get all traits used by a class (including nested traits)
     *
     * @param  ReflectionClass<T>  $class
     * @return list<string>
     *
     * @throws ReflectionException
     */
    private function getAllTraits(ReflectionClass $class): array
    {
        $traits = [];

        // Get direct traits
        foreach ($class->getTraitNames() as $traitName) {
            $traits[] = $traitName;

            // Get nested traits (traits used by traits)
            /** @var ReflectionClass<T> $traitReflection */
            $traitReflection = new ReflectionClass($traitName);
            $traits = array_merge($traits, $this->getAllTraits($traitReflection));
        }

        return array_unique($traits);
    }
}
