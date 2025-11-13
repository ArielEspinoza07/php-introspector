<?php

declare(strict_types=1);

namespace Introspector;

use Introspector\Enums\MemberType;
use Introspector\Enums\SourceType;
use Introspector\VOs\Shared\DeclaringSource;
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
        // Get only direct traits (not nested)
        $traits = $class->getTraits();

        foreach ($traits as $trait) {
            // DEPTH-FIRST: Check nested traits FIRST before checking current trait
            $nestedResult = $this->findInTraits($trait, $memberType, $memberName);
            if ($nestedResult !== null) {
                return $nestedResult;
            }

            // Then check if THIS specific trait DECLARES the member (not just has it)
            if ($this->isDeclaredDirectlyInClass($trait, $memberType, $memberName)) {
                return new DeclaringSource(
                    type: SourceType::Trait_,
                    className: $trait->getName(),
                    shortName: $trait->getShortName(),
                    namespace: $trait->getNamespaceName(),
                );
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
        $interfaces = $class->getInterfaces();

        foreach ($interfaces as $interface) {
            $nestedResult = $this->findInInterfaces($interface, $memberType, $memberName);
            if ($nestedResult !== null) {
                return $nestedResult;
            }

            if ($this->isDeclaredDirectlyInClass($interface, $memberType, $memberName)) {
                return new DeclaringSource(
                    type: SourceType::Interface_,
                    className: $interface->getName(),
                    shortName: $interface->getShortName(),
                    namespace: $interface->getNamespaceName(),
                );
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
     * Check if a member is declared DIRECTLY in a class/trait (not inherited)
     *
     * @param  ReflectionClass<T>  $class
     */
    private function isDeclaredDirectlyInClass(ReflectionClass $class, MemberType $memberType, string $memberName): bool
    {
        try {
            $declaringClass = match ($memberType) {
                MemberType::Property => $class->getProperty($memberName)->getDeclaringClass(),
                MemberType::Method => $class->getMethod($memberName)->getDeclaringClass(),
                MemberType::Constant => (new ReflectionClassConstant($class->getName(), $memberName))->getDeclaringClass(),
            };

            // Return true only if THIS class/trait is the one that declares the member
            return $declaringClass->getName() === $class->getName();
        } catch (ReflectionException) {
            // Member doesn't exist in this class/trait
            return false;
        }
    }
}
