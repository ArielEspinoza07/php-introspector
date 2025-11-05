<?php

declare(strict_types=1);

use Aurora\Reflection\DTO\ClassMetadata;
use Aurora\Reflection\ReaderFactory;
use Aurora\Reflection\ReflectionClassReader;
use Aurora\Reflection\Tests\Fixtures\Vehicle;

it('returns ReflectionClassReader', function () {
    $reader = ReaderFactory::createClassReader();

    expect($reader)->toBeInstanceOf(ReflectionClassReader::class);
});

it('throws exception when does not exist', function () {
    $reader = ReaderFactory::createClassReader();

    $reader->getClassMetadata('V6Engine');
})->throws(ReflectionException::class);

it('returns class metadata', function () {
    $reader = ReaderFactory::createClassReader();

    $classMetaData = $reader->getClassMetadata(Vehicle::class);

    expect($classMetaData)->toBeInstanceOf(ClassMetadata::class)
        ->and($classMetaData->hasConstructor())->toBeTrue()
        ->and($classMetaData->hasProperties())->toBeTrue()
        ->and($classMetaData->hasMethods())->toBeTrue()
        ->and($classMetaData->hasAttributes())->toBeFalse()
        ->and($classMetaData->requiresInjection())->toBeTrue();
});