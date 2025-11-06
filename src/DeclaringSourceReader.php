<?php

declare(strict_types=1);

namespace Aurora\Reflection;

use Aurora\Reflection\Enums\MemberType;
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

        $traitSource = $this->findInTraits($currentClass, MemberType::Property, $propertyName);
        if ($traitSource !== null) {
            return $traitSource;
        }

        $interfaceSource = $this->findInInterfaces($currentClass, MemberType::Property, $propertyName);
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

        $traitSource = $this->findInTraits($currentClass, MemberType::Method, $methodName);
        if ($traitSource !== null) {
            return $traitSource;
        }

        $interfaceSource = $this->findInInterfaces($currentClass, MemberType::Method, $methodName);
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

        $traitSource = $this->findInTraits($currentClass, MemberType::Constant, $constantName);
        if ($traitSource !== null) {
            return $traitSource;
        }

        $interfaceSource = $this->findInInterfaces($currentClass, MemberType::Constant, $constantName);
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
    private function findInTraits(ReflectionClass $class, MemberType $memberType, string $memberName): ?DeclaringSource
    {
        $traits = $this->getAllTraits($class);

        foreach ($traits as $trait) {
            $found = $this->findMember($trait, $memberType, $memberName);

            if ($found) {
                return new DeclaringSource(
                    type: SourceType::Trait_,
                    className: $trait->getName(),
                    shortName: $trait->getShortName(),
                    namespace: $trait->getNamespaceName(),
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
    private function findInInterfaces(ReflectionClass $class, MemberType $memberType, string $memberName): ?DeclaringSource
    {
        $interfaces = $this->getAllInterfaces($class);

        foreach ($interfaces as $interface) {
            $found = $this->findMember($interface, $memberType, $memberName);

            if ($found) {
                return new DeclaringSource(
                    type: SourceType::Interface_,
                    className: $interface->getName(),
                    shortName: $interface->getShortName(),
                    namespace: $interface->getNamespaceName(),
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
                namespace: $declaringClass->getNamespaceName(),
            );
        }

        // Otherwise, it's from a parent class
        return new DeclaringSource(
            type: SourceType::Parent_,
            className: $declaringClassName,
            shortName: $declaringClass->getShortName(),
            namespace: $declaringClass->getNamespaceName(),
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

    /**
     * @param  ReflectionClass<T>  $class
     */
    private function findMember(ReflectionClass $class, MemberType $memberType, string $memberName): bool
    {
        return match ($memberType) {
            MemberType::Property => $class->hasProperty($memberName),
            MemberType::Method => $class->hasMethod($memberName),
            MemberType::Constant => $class->hasConstant($memberName),
        };
    }
}
