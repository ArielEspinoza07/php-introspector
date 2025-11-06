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
        $propertyName = $property->getName();
        $declaringClass = $property->getDeclaringClass();

        $traitSource = $this->findInTraits($currentClass, 'property', $propertyName);
        if ($traitSource !== null) {
            return $traitSource;
        }

        $interfaceSource = $this->findInInterfaces($currentClass, 'property', $propertyName);
        if ($interfaceSource !== null) {
            return $interfaceSource;
        }

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
        $methodName = $method->getName();
        $declaringClass = $method->getDeclaringClass();

        $traitSource = $this->findInTraits($currentClass, 'method', $methodName);
        if ($traitSource !== null) {
            return $traitSource;
        }

        $interfaceSource = $this->findInInterfaces($currentClass, 'method', $methodName);
        if ($interfaceSource !== null) {
            return $interfaceSource;
        }

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
        $constantName = $constant->getName();
        $declaringClass = $constant->getDeclaringClass();

        $traitSource = $this->findInTraits($currentClass, 'constant', $constantName);
        if ($traitSource !== null) {
            return $traitSource;
        }

        $interfaceSource = $this->findInInterfaces($currentClass, 'constant', $constantName);
        if ($interfaceSource !== null) {
            return $interfaceSource;
        }

        return $this->createDeclaringSource($declaringClass, $currentClass);
    }

    /**
     * Find a member (property, method, constant) in the traits used by a class
     *
     * @param  ReflectionClass<T>  $class
     *
     * @throws ReflectionException
     */
    private function findInTraits(ReflectionClass $class, string $memberType, string $memberName): ?DeclaringSource
    {
        $traits = $this->getAllTraits($class);

        foreach ($traits as $trait) {
            $found = match ($memberType) {
                'property' => $trait->hasProperty($memberName),
                'method' => $trait->hasMethod($memberName),
                'constant' => $trait->hasConstant($memberName),
            };

            if ($found) {
                return new DeclaringSource(
                    type: SourceType::Trait_,
                    className: $trait->getName(),
                    shortName: $trait->getShortName(),
                );
            }

            // Check nested traits recursively
            $nestedResult = $this->findInTraits($trait, $memberType, $memberName);
            if ($nestedResult !== null) {
                return $nestedResult;
            }
        }

        return null;
    }

    /**
     * Find a member (property, method, constant) in the interfaces used by a class
     *
     * @param  ReflectionClass<T>  $class
     *
     * @throws ReflectionException
     */
    private function findInInterfaces(ReflectionClass $class, string $memberType, string $memberName): ?DeclaringSource
    {
        $interfaces = $this->getAllInterfaces($class);

        foreach ($interfaces as $interface) {
            $found = match ($memberType) {
                'property' => $interface->hasProperty($memberName),
                'method' => $interface->hasMethod($memberName),
                'constant' => $interface->hasConstant($memberName),
            };

            if ($found) {
                return new DeclaringSource(
                    type: SourceType::Interface_,
                    className: $interface->getName(),
                    shortName: $interface->getShortName(),
                );
            }

            // Check nested traits recursively
            $nestedResult = $this->findInInterfaces($interface, $memberType, $memberName);
            if ($nestedResult !== null) {
                return $nestedResult;
            }
        }

        return null;
    }

    /**
     * Create a DeclaringSource VO by determining the source type
     *
     * @param  ReflectionClass<T>  $declaringClass
     * @param  ReflectionClass<T>  $currentClass
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
     * @return list<ReflectionClass<T>>
     *
     * @throws ReflectionException
     */
    private function getAllTraits(ReflectionClass $class): array
    {
        $traits = [];

        foreach ($class->getTraits() as $trait) {
            $traits[] = $trait;
            $traits = array_merge($traits, $this->getAllTraits($trait));
        }

        return array_values(array_unique($traits));
    }

    /**
     * Get all interfaces used by a class (including nested interfaces)
     *
     * @param  ReflectionClass<T>  $class
     * @return list<ReflectionClass<T>>
     *
     * @throws ReflectionException
     */
    private function getAllInterfaces(ReflectionClass $class): array
    {
        $interfaces = [];

        foreach ($class->getInterfaces() as $interface) {
            $interfaces[] = $interface;
            $interfaces = array_merge($interfaces, $this->getAllInterfaces($interface));
        }

        return array_values(array_unique($interfaces));
    }
}
