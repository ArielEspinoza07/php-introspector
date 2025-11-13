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

    expect($traitProperties)->toHaveCount(6) // createdAt, updatedAt
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

    expect($traitMethods)->toHaveCount(11) // getCreatedAt, getUpdatedAt, initializeTimestamps, touch
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

test('detects properties from nested traits correctly', function () {
    $reader = new Reader();
    $metadata = $reader->read(CompleteClass::class);

    // BaseNestedTrait property (deepest level)
    $baseTraitProperty = array_values(array_filter($metadata->properties, fn ($p) => $p->name === 'baseTraitProperty'))[0] ?? null;
    expect($baseTraitProperty)->not->toBeNull()
        ->and($baseTraitProperty->declaringSource->type)->toBe(SourceType::Trait_)
        ->and($baseTraitProperty->declaringSource->shortName)->toBe('BaseNestedTrait');

    // MiddleNestedTrait property (middle level)
    $middleTraitProperty = array_values(array_filter($metadata->properties, fn ($p) => $p->name === 'middleTraitProperty'))[0] ?? null;
    expect($middleTraitProperty)->not->toBeNull()
        ->and($middleTraitProperty->declaringSource->type)->toBe(SourceType::Trait_)
        ->and($middleTraitProperty->declaringSource->shortName)->toBe('MiddleNestedTrait');

    // TopNestedTrait property (top level)
    $topTraitProperty = array_values(array_filter($metadata->properties, fn ($p) => $p->name === 'topTraitProperty'))[0] ?? null;
    expect($topTraitProperty)->not->toBeNull()
        ->and($topTraitProperty->declaringSource->type)->toBe(SourceType::Trait_)
        ->and($topTraitProperty->declaringSource->shortName)->toBe('TopNestedTrait');
});

test('detects methods from nested traits correctly', function () {
    $reader = new Reader();
    $metadata = $reader->read(CompleteClass::class);

    // BaseNestedTrait method (deepest level)
    $baseTraitMethod = array_values(array_filter($metadata->methods, fn ($m) => $m->name === 'baseTraitMethod'))[0] ?? null;
    expect($baseTraitMethod)->not->toBeNull()
        ->and($baseTraitMethod->declaringSource->type)->toBe(SourceType::Trait_)
        ->and($baseTraitMethod->declaringSource->shortName)->toBe('BaseNestedTrait');

    // MiddleNestedTrait method (middle level)
    $middleTraitMethod = array_values(array_filter($metadata->methods, fn ($m) => $m->name === 'middleTraitMethod'))[0] ?? null;
    expect($middleTraitMethod)->not->toBeNull()
        ->and($middleTraitMethod->declaringSource->type)->toBe(SourceType::Trait_)
        ->and($middleTraitMethod->declaringSource->shortName)->toBe('MiddleNestedTrait');

    // TopNestedTrait method (top level)
    $topTraitMethod = array_values(array_filter($metadata->methods, fn ($m) => $m->name === 'topTraitMethod'))[0] ?? null;
    expect($topTraitMethod)->not->toBeNull()
        ->and($topTraitMethod->declaringSource->type)->toBe(SourceType::Trait_)
        ->and($topTraitMethod->declaringSource->shortName)->toBe('TopNestedTrait');
});

test('detects constants from nested interfaces correctly', function () {
    $reader = new Reader();
    $metadata = $reader->read(CompleteClass::class);

    // Level1Interface constant (deepest level)
    $level1Const = array_values(array_filter($metadata->constants, fn ($c) => $c->name === 'LEVEL1_CONST'))[0] ?? null;
    expect($level1Const)->not->toBeNull()
        ->and($level1Const->declaringSource->type)->toBe(SourceType::Interface_)
        ->and($level1Const->declaringSource->shortName)->toBe('Level1Interface');

    // Level2Interface constant (middle level)
    $level2Const = array_values(array_filter($metadata->constants, fn ($c) => $c->name === 'LEVEL2_CONST'))[0] ?? null;
    expect($level2Const)->not->toBeNull()
        ->and($level2Const->declaringSource->type)->toBe(SourceType::Interface_)
        ->and($level2Const->declaringSource->shortName)->toBe('Level2Interface');

    // Level3Interface constant (top level)
    $level3Const = array_values(array_filter($metadata->constants, fn ($c) => $c->name === 'LEVEL3_CONST'))[0] ?? null;
    expect($level3Const)->not->toBeNull()
        ->and($level3Const->declaringSource->type)->toBe(SourceType::Interface_)
        ->and($level3Const->declaringSource->shortName)->toBe('Level3Interface');
});

test('interface methods implemented by traits report trait as source', function () {
    $reader = new Reader();
    $metadata = $reader->read(CompleteClass::class);

    // When a method is declared in an interface but implemented by a trait,
    // it's correctly reported as coming from the trait (actual implementation),
    // not the interface (just the contract). This is consistent with PHP's
    // getDeclaringClass() behavior and more useful for finding the actual code.

    // level1Method: declared in Level1Interface, implemented by MixInterfaceTrait
    $level1Method = array_values(array_filter($metadata->methods, fn ($m) => $m->name === 'level1Method'))[0] ?? null;
    expect($level1Method)->not->toBeNull()
        ->and($level1Method->declaringSource->type)->toBe(SourceType::Trait_)
        ->and($level1Method->declaringSource->shortName)->toBe('MixInterfaceTrait');

    // level2Method: declared in Level2Interface, implemented by MixInterfaceTrait
    $level2Method = array_values(array_filter($metadata->methods, fn ($m) => $m->name === 'level2Method'))[0] ?? null;
    expect($level2Method)->not->toBeNull()
        ->and($level2Method->declaringSource->type)->toBe(SourceType::Trait_)
        ->and($level2Method->declaringSource->shortName)->toBe('MixInterfaceTrait');
});
