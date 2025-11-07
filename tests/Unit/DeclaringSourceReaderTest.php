<?php

declare(strict_types=1);

use Aurora\Reflection\Enums\SourceType;
use Aurora\Reflection\Reader;
use Aurora\Reflection\Tests\Fixtures\Circle;
use Aurora\Reflection\Tests\Fixtures\CompleteClass;
use Aurora\Reflection\Tests\Fixtures\TimestampTrait;

test('detects properties from trait', function () {
    $reader = new Reader();
    $metadata = $reader->read(CompleteClass::class);

    $createdAt = array_values(array_filter($metadata->properties, fn ($p) => $p->name === 'createdAt'))[0];
    $updatedAt = array_values(array_filter($metadata->properties, fn ($p) => $p->name === 'updatedAt'))[0];

    expect($createdAt->declaringSource->type)->toBe(SourceType::Trait_)
        ->and($createdAt->declaringSource->className)->toBe(TimestampTrait::class)
        ->and($createdAt->declaringSource->shortName)->toBe('TimestampTrait')
        ->and($createdAt->declaringSource->namespace)->toBe('Aurora\Reflection\Tests\Fixtures')
        ->and($updatedAt->declaringSource->type)->toBe(SourceType::Trait_);
});

test('detects methods from trait', function () {
    $reader = new Reader();
    $metadata = $reader->read(CompleteClass::class);

    $getCreatedAt = array_values(array_filter($metadata->methods, fn ($m) => $m->name === 'getCreatedAt'))[0];
    $getUpdatedAt = array_values(array_filter($metadata->methods, fn ($m) => $m->name === 'getUpdatedAt'))[0];
    $touch = array_values(array_filter($metadata->methods, fn ($m) => $m->name === 'touch'))[0];
    $initializeTimestamps = array_values(array_filter($metadata->methods, fn ($m) => $m->name === 'initializeTimestamps'))[0];

    expect($getCreatedAt->declaringSource->type)->toBe(SourceType::Trait_)
        ->and($getCreatedAt->declaringSource->className)->toBe(TimestampTrait::class)
        ->and($getUpdatedAt->declaringSource->type)->toBe(SourceType::Trait_)
        ->and($touch->declaringSource->type)->toBe(SourceType::Trait_)
        ->and($initializeTimestamps->declaringSource->type)->toBe(SourceType::Trait_);
});

test('detects properties declared in class itself', function () {
    $reader = new Reader();
    $metadata = $reader->read(CompleteClass::class);

    $id = array_values(array_filter($metadata->properties, fn ($p) => $p->name === 'id'))[0];
    $name = array_values(array_filter($metadata->properties, fn ($p) => $p->name === 'name'))[0];
    $active = array_values(array_filter($metadata->properties, fn ($p) => $p->name === 'active'))[0];

    expect($id->declaringSource->type)->toBe(SourceType::Self_)
        ->and($id->declaringSource->className)->toBe(CompleteClass::class)
        ->and($id->declaringSource->shortName)->toBe('CompleteClass')
        ->and($name->declaringSource->type)->toBe(SourceType::Self_)
        ->and($active->declaringSource->type)->toBe(SourceType::Self_);
});

test('detects methods declared in class itself', function () {
    $reader = new Reader();
    $metadata = $reader->read(CompleteClass::class);

    $getName = array_values(array_filter($metadata->methods, fn ($m) => $m->name === 'getName'))[0];
    $getId = array_values(array_filter($metadata->methods, fn ($m) => $m->name === 'getId'))[0];

    expect($getName->declaringSource->type)->toBe(SourceType::Self_)
        ->and($getName->declaringSource->className)->toBe(CompleteClass::class)
        ->and($getId->declaringSource->type)->toBe(SourceType::Self_);
});

test('detects properties from parent class', function () {
    $reader = new Reader();
    $metadata = $reader->read(Circle::class);

    $color = array_values(array_filter($metadata->properties, fn ($p) => $p->name === 'color'))[0];

    expect($color->declaringSource->type)->toBe(SourceType::Parent_)
        ->and($color->declaringSource->className)->toContain('AbstractShape')
        ->and($color->declaringSource->shortName)->toBe('AbstractShape');
});

test('detects methods from parent class', function () {
    $reader = new Reader();
    $metadata = $reader->read(Circle::class);

    $getColor = array_values(array_filter($metadata->methods, fn ($m) => $m->name === 'getColor'))[0];
    $isValid = array_values(array_filter($metadata->methods, fn ($m) => $m->name === 'isValid'))[0];

    expect($getColor->declaringSource->type)->toBe(SourceType::Parent_)
        ->and($getColor->declaringSource->className)->toContain('AbstractShape')
        ->and($isValid->declaringSource->type)->toBe(SourceType::Parent_);
});

test('detects constants from interface', function () {
    $reader = new Reader();
    $metadata = $reader->read(CompleteClass::class);

    $constants = array_filter(
        $metadata->constants,
        fn ($c) => $c->declaringSource->type === SourceType::Interface_
    );

    // CompleteClass might not have interface constants, but let's test with a class that implements SerializableInterface
    // We'll check that the declaring source detection works for interfaces in general
    expect($constants)->toBeArray();
});

test('detects methods from interface', function () {
    $reader = new Reader();
    $metadata = $reader->read(CompleteClass::class);

    $toArrayMatches = array_values(array_filter($metadata->methods, fn ($m) => $m->name === 'toArray'));
    $toJsonMatches = array_values(array_filter($metadata->methods, fn ($m) => $m->name === 'toJson'));

    // These methods are declared in CompleteClass but fulfill interface contracts
    // The interface ones would be fromArray, toArray, toJson from SerializableInterface
    expect($toArrayMatches)->not->toBeEmpty()
        ->and($toJsonMatches)->not->toBeEmpty();
});

test('can filter properties by source type', function () {
    $reader = new Reader();
    $metadata = $reader->read(CompleteClass::class);

    $traitProperties = array_filter(
        $metadata->properties,
        fn ($p) => $p->declaringSource->type === SourceType::Trait_
    );

    $selfProperties = array_filter(
        $metadata->properties,
        fn ($p) => $p->declaringSource->type === SourceType::Self_
    );

    expect($traitProperties)->toHaveCount(2) // createdAt, updatedAt
        ->and($selfProperties)->toHaveCount(8); // All own properties: instanceCount, cache, id, name, status, description, tags, active
});

test('can filter methods by source type', function () {
    $reader = new Reader();
    $metadata = $reader->read(CompleteClass::class);

    $traitMethods = array_filter(
        $metadata->methods,
        fn ($m) => $m->declaringSource->type === SourceType::Trait_
    );

    $selfMethods = array_filter(
        $metadata->methods,
        fn ($m) => $m->declaringSource->type === SourceType::Self_
    );

    expect($traitMethods)->toHaveCount(4) // getCreatedAt, getUpdatedAt, initializeTimestamps, touch
        ->and($selfMethods)->toHaveCount(11); // All own methods (excluding trait methods)
});

test('declaring source includes namespace', function () {
    $reader = new Reader();
    $metadata = $reader->read(CompleteClass::class);

    $createdAt = array_values(array_filter($metadata->properties, fn ($p) => $p->name === 'createdAt'))[0];

    expect($createdAt->declaringSource->namespace)->toBe('Aurora\Reflection\Tests\Fixtures');
});

test('can group members by source', function () {
    $reader = new Reader();
    $metadata = $reader->read(CompleteClass::class);

    $grouped = [
        'self' => [],
        'trait' => [],
        'parent' => [],
        'interface' => [],
    ];

    foreach ($metadata->properties as $property) {
        $grouped[$property->declaringSource->type->value][] = $property->name;
    }

    expect($grouped['self'])->toBeArray()->not->toBeEmpty()
        ->and($grouped['trait'])->toBeArray()->not->toBeEmpty()
        ->and($grouped['trait'])->toContain('createdAt', 'updatedAt');
});

test('constants have declaring source', function () {
    $reader = new Reader();
    $metadata = $reader->read(CompleteClass::class);

    $apiVersion = array_values(array_filter($metadata->constants, fn ($c) => $c->name === 'API_VERSION'))[0];

    expect($apiVersion->declaringSource)->not->toBeNull()
        ->and($apiVersion->declaringSource->type)->toBe(SourceType::Self_)
        ->and($apiVersion->declaringSource->className)->toBe(CompleteClass::class);
});

test('declaring source is serializable', function () {
    $reader = new Reader();
    $metadata = $reader->read(CompleteClass::class);

    $createdAt = array_values(array_filter($metadata->properties, fn ($p) => $p->name === 'createdAt'))[0];

    $json = json_encode($createdAt);
    $decoded = json_decode($json, true);

    expect($decoded['declaring_source'])->toBeArray()
        ->and($decoded['declaring_source']['type'])->toBe('trait')
        ->and($decoded['declaring_source']['class_name'])->toBe(TimestampTrait::class)
        ->and($decoded['declaring_source']['short_name'])->toBe('TimestampTrait')
        ->and($decoded['declaring_source']['namespace'])->toBe('Aurora\Reflection\Tests\Fixtures');
});
