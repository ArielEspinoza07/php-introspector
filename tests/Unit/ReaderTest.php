<?php

declare(strict_types=1);

use Aurora\Reflection\Enums\ClassType;
use Aurora\Reflection\Enums\SourceType;
use Aurora\Reflection\Enums\Visibility;
use Aurora\Reflection\Reader;
use Aurora\Reflection\Tests\Fixtures\AbstractShape;
use Aurora\Reflection\Tests\Fixtures\Circle;
use Aurora\Reflection\Tests\Fixtures\CompleteClass;
use Aurora\Reflection\Tests\Fixtures\SerializableInterface;
use Aurora\Reflection\Tests\Fixtures\Status;
use Aurora\Reflection\Tests\Fixtures\TimestampTrait;

test('can read complete class metadata', function () {
    $reader = new Reader();
    $metadata = $reader->read(CompleteClass::class);

    expect($metadata->class->name)->toBe(CompleteClass::class)
        ->and($metadata->class->shortName)->toBe('CompleteClass')
        ->and($metadata->class->nameSpace)->toBe('Aurora\Reflection\Tests\Fixtures')
        ->and($metadata->class->type)->toBe(ClassType::Class_)
        ->and($metadata->class->modifier->isFinal)->toBeTrue()
        ->and($metadata->class->modifier->isAbstract)->toBeFalse();
});

test('reads class with trait usage', function () {
    $reader = new Reader();
    $metadata = $reader->read(CompleteClass::class);

    $traits = $metadata->class->traits;

    expect($traits)->toHaveCount(1)
        ->and($traits[0]->type->value)->toBe('trait')
        ->and($traits[0]->className)->toBe(TimestampTrait::class)
        ->and($traits[0]->shortName)->toBe('TimestampTrait');
});

test('reads class with interface implementation', function () {
    $reader = new Reader();
    $metadata = $reader->read(CompleteClass::class);

    $implements = $metadata->class->implements;

    expect($implements)->toHaveCount(3)
        ->and($implements[0]->type->value)->toBe('interface')
        ->and($implements[0]->className)->toContain('JsonSerializable');
});

test('reads class docblock', function () {
    $reader = new Reader();
    $metadata = $reader->read(CompleteClass::class);

    expect($metadata->class->docBlock)->not->toBeNull()
        ->and($metadata->class->docBlock->summary)->toBe('Complete class with all possible PHP features')
        ->and($metadata->class->docBlock->custom)->toHaveCount(2);
});

test('reads all class properties including trait properties', function () {
    $reader = new Reader();
    $metadata = $reader->read(CompleteClass::class);

    $propertyNames = array_map(fn ($p) => $p->name, $metadata->properties);

    expect($metadata->properties)->toHaveCount(10)
        ->and($propertyNames)->toContain('id', 'name', 'status', 'createdAt', 'updatedAt');
});

test('detects promoted properties', function () {
    $reader = new Reader();
    $metadata = $reader->read(CompleteClass::class);

    $activeProperty = array_values(array_filter($metadata->properties, fn ($p) => $p->name === 'active'))[0];

    expect($activeProperty)->not->toBeNull()
        ->and($activeProperty->modifier->isPromoted)->toBeTrue()
        ->and($activeProperty->modifier->isReadonly)->toBeTrue();
});

test('reads property types correctly', function () {
    $reader = new Reader();
    $metadata = $reader->read(CompleteClass::class);

    $idProperty = array_values(array_filter($metadata->properties, fn ($p) => $p->name === 'id'))[0];
    $descriptionProperty = array_values(array_filter($metadata->properties, fn ($p) => $p->name === 'description'))[0];

    expect($idProperty->type)->not->toBeNull()
        ->and($idProperty->type->name)->toBe('int')
        ->and($idProperty->type->isBuiltin)->toBeTrue()
        ->and($descriptionProperty->type->isNullable)->toBeTrue();
});

test('reads static properties', function () {
    $reader = new Reader();
    $metadata = $reader->read(CompleteClass::class);

    $instanceCount = array_values(array_filter($metadata->properties, fn ($p) => $p->name === 'instanceCount'))[0];

    expect($instanceCount)->not->toBeNull()
        ->and($instanceCount->modifier->isStatic)->toBeTrue()
        ->and($instanceCount->modifier->visibility)->toBe(Visibility::Public);
});

test('reads all class constants', function () {
    $reader = new Reader();
    $metadata = $reader->read(CompleteClass::class);

    expect($metadata->constants)->toHaveCount(7);

    $constantNames = array_map(fn ($c) => $c->name, $metadata->constants);
    expect($constantNames)->toContain('API_VERSION', 'DEBUG', 'MAX_RETRIES', 'SECRET');
});

test('reads constant with different visibilities', function () {
    $reader = new Reader();
    $metadata = $reader->read(CompleteClass::class);

    $apiVersion = array_values(array_filter($metadata->constants, fn ($c) => $c->name === 'API_VERSION'))[0];
    $maxRetries = array_values(array_filter($metadata->constants, fn ($c) => $c->name === 'MAX_RETRIES'))[0];
    $secret = array_values(array_filter($metadata->constants, fn ($c) => $c->name === 'SECRET'))[0];

    expect($apiVersion->visibility)->toBe(Visibility::Public)
        ->and($maxRetries->visibility)->toBe(Visibility::Protected)
        ->and($secret->visibility)->toBe(Visibility::Private);
});

test('reads final constants', function () {
    $reader = new Reader();
    $metadata = $reader->read(CompleteClass::class);

    $debug = array_values(array_filter($metadata->constants, fn ($c) => $c->name === 'DEBUG'))[0];

    expect($debug->isFinal)->toBeTrue();
});

test('reads all class methods including trait methods', function () {
    $reader = new Reader();
    $metadata = $reader->read(CompleteClass::class);

    $methodNames = array_map(fn ($m) => $m->name, $metadata->methods);

    expect($metadata->methods)->toHaveCount(20) // Own methods + trait methods
        ->and($methodNames)->toContain('getName', 'touch', 'getCreatedAt');
});

test('reads method visibility', function () {
    $reader = new Reader();
    $metadata = $reader->read(CompleteClass::class);

    $publicMethod = array_values(array_filter($metadata->methods, fn ($m) => $m->name === 'getName'))[0];
    $protectedMethod = array_values(array_filter($metadata->methods, fn ($m) => $m->name === 'getCached'))[0];
    $privateMethod = array_values(array_filter($metadata->methods, fn ($m) => $m->name === 'clearCache'))[0];

    expect($publicMethod->modifier->visibility)->toBe(Visibility::Public)
        ->and($protectedMethod->modifier->visibility)->toBe(Visibility::Protected)
        ->and($privateMethod->modifier->visibility)->toBe(Visibility::Private);
});

test('reads static methods', function () {
    $reader = new Reader();
    $metadata = $reader->read(CompleteClass::class);

    $builderMethod = array_values(array_filter($metadata->methods, fn ($m) => $m->name === 'builder'))[0];

    expect($builderMethod->modifier->isStatic)->toBeTrue();
});

test('reads method return types', function () {
    $reader = new Reader();
    $metadata = $reader->read(CompleteClass::class);

    $getIdMethod = array_values(array_filter($metadata->methods, fn ($m) => $m->name === 'getId'))[0];
    $findMethod = array_values(array_filter($metadata->methods, fn ($m) => $m->name === 'find'))[0];

    expect($getIdMethod->returnType)->not->toBeNull()
        ->and($getIdMethod->returnType->name)->toBe('int')
        ->and($findMethod->returnType->isNullable)->toBeTrue();
});

test('reads method with special return type self', function () {
    $reader = new Reader();
    $metadata = $reader->read(CompleteClass::class);

    $withNameMethod = array_values(array_filter($metadata->methods, fn ($m) => $m->name === 'withName'))[0];

    expect($withNameMethod->returnType)->not->toBeNull()
        ->and($withNameMethod->returnType->name)->toBe('self')
        ->and($withNameMethod->returnType->isSpecial)->toBeTrue()
        ->and($withNameMethod->returnType->resolvedName)->toBe(CompleteClass::class);
});

test('reads method with special return type static', function () {
    $reader = new Reader();
    $metadata = $reader->read(CompleteClass::class);

    $builderMethod = array_values(array_filter($metadata->methods, fn ($m) => $m->name === 'builder'))[0];

    expect($builderMethod->returnType)->not->toBeNull()
        ->and($builderMethod->returnType->name)->toBe('static')
        ->and($builderMethod->returnType->isSpecial)->toBeTrue();
});

test('reads method parameters', function () {
    $reader = new Reader();
    $metadata = $reader->read(CompleteClass::class);

    $withNameMethod = array_values(array_filter($metadata->methods, fn ($m) => $m->name === 'withName'))[0];

    expect($withNameMethod->parameters)->toHaveCount(1)
        ->and($withNameMethod->parameters[0]->name)->toBe('name')
        ->and($withNameMethod->parameters[0]->type->name)->toBe('string');
});

test('reads variadic parameters', function () {
    $reader = new Reader();
    $metadata = $reader->read(CompleteClass::class);

    $createMethod = array_values(array_filter($metadata->methods, fn ($m) => $m->name === 'create'))[0];
    $tagsParam = array_values(array_filter($createMethod->parameters, fn ($p) => $p->name === 'tags'))[0];

    expect($tagsParam->isVariadic)->toBeTrue();
});

test('reads parameter with reference', function () {
    $reader = new Reader();
    $metadata = $reader->read(CompleteClass::class);

    $incrementMethod = array_values(array_filter($metadata->methods, fn ($m) => $m->name === 'increment'))[0];
    $counterParam = $incrementMethod->parameters[0];

    expect($counterParam->isPassedByReference)->toBeTrue();
});

test('reads method docblocks', function () {
    $reader = new Reader();
    $metadata = $reader->read(CompleteClass::class);

    $getIdMethod = array_values(array_filter($metadata->methods, fn ($m) => $m->name === 'getId'))[0];

    expect($getIdMethod->docBlock)->not->toBeNull()
        ->and($getIdMethod->docBlock->summary)->toBe('Get the entity ID')
        ->and($getIdMethod->docBlock->return)->not->toBeNull()
        ->and($getIdMethod->docBlock->return->type)->toBe('int');
});

test('reads constructor metadata', function () {
    $reader = new Reader();
    $metadata = $reader->read(CompleteClass::class);

    expect($metadata->constructor)->not->toBeNull()
        ->and($metadata->constructor->parameters)->toHaveCount(7)
        ->and($metadata->constructor->modifier->visibility)->toBe(Visibility::Public);
});

test('reads constructor promoted parameters', function () {
    $reader = new Reader();
    $metadata = $reader->read(CompleteClass::class);

    $activeParam = $metadata->constructor->parameters[0];

    expect($activeParam->name)->toBe('active')
        ->and($activeParam->isPromoted)->toBeTrue();
});

test('reads abstract class', function () {
    $reader = new Reader();
    $metadata = $reader->read(AbstractShape::class);

    expect($metadata->class->type)->toBe(ClassType::Class_)
        ->and($metadata->class->modifier->isAbstract)->toBeTrue();
});

test('reads abstract methods', function () {
    $reader = new Reader();
    $metadata = $reader->read(AbstractShape::class);

    $areaMethod = array_values(array_filter($metadata->methods, fn ($m) => $m->name === 'area'))[0];

    expect($areaMethod->modifier->isAbstract)->toBeTrue();
});

test('reads final methods', function () {
    $reader = new Reader();
    $metadata = $reader->read(AbstractShape::class);

    $getColorMethod = array_values(array_filter($metadata->methods, fn ($m) => $m->name === 'getColor'))[0];

    expect($getColorMethod->modifier->isFinal)->toBeTrue();
});

test('reads class with parent', function () {
    $reader = new Reader();
    $metadata = $reader->read(Circle::class);

    expect($metadata->class->extends)->not->toBeNull()
        ->and($metadata->class->extends)->toBe(AbstractShape::class);
});

test('reads inherited properties', function () {
    $reader = new Reader();
    $metadata = $reader->read(Circle::class);

    $colorProperty = array_values(array_filter($metadata->properties, fn ($p) => $p->name === 'color'))[0];

    expect($colorProperty)->not->toBeNull()
        ->and($colorProperty->declaringSource->type)->toBe(SourceType::Parent_);
});

test('reads method with parent return type', function () {
    $reader = new Reader();
    $metadata = $reader->read(Circle::class);

    $getParentMethod = array_values(array_filter($metadata->methods, fn ($m) => $m->name === 'getParent'))[0];

    expect($getParentMethod->returnType)->not->toBeNull()
        ->and($getParentMethod->returnType->name)->toBe('parent')
        ->and($getParentMethod->returnType->isSpecial)->toBeTrue();
});

test('reads enum metadata', function () {
    $reader = new Reader();
    $metadata = $reader->read(Status::class);

    expect($metadata->class->type)->toBe(ClassType::Enum_)
        ->and($metadata->class->shortName)->toBe('Status');
});

test('reads interface metadata', function () {
    $reader = new Reader();
    $metadata = $reader->read(SerializableInterface::class);

    expect($metadata->class->type)->toBe(ClassType::Interface_)
        ->and($metadata->class->shortName)->toBe('SerializableInterface');
});

test('reads interface constants', function () {
    $reader = new Reader();
    $metadata = $reader->read(SerializableInterface::class);

    expect($metadata->constants)->toHaveCount(3);

    $constantNames = array_map(fn ($c) => $c->name, $metadata->constants);
    expect($constantNames)->toContain('VERSION', 'FORMAT_JSON', 'FORMAT_ARRAY');
});

test('reads interface methods', function () {
    $reader = new Reader();
    $metadata = $reader->read(SerializableInterface::class);

    expect($metadata->methods)->toHaveCount(3);

    $methodNames = array_map(fn ($m) => $m->name, $metadata->methods);
    expect($methodNames)->toContain('fromArray', 'toArray', 'toJson');
});

test('reads trait metadata', function () {
    $reader = new Reader();
    $metadata = $reader->read(TimestampTrait::class);

    expect($metadata->class->type)->toBe(ClassType::Trait_)
        ->and($metadata->class->shortName)->toBe('TimestampTrait');
});

test('metadata is json serializable', function () {
    $reader = new Reader();
    $metadata = $reader->read(CompleteClass::class);

    $json = json_encode($metadata);

    expect($json)->toBeString()
        ->and(json_decode($json, true))->toBeArray()
        ->and(json_last_error())->toBe(JSON_ERROR_NONE);
});

test('metadata can be converted to array', function () {
    $reader = new Reader();
    $metadata = $reader->read(CompleteClass::class);

    $array = $metadata->toArray();

    expect($array)->toBeArray()
        ->and($array)->toHaveKeys(['class', 'constructor', 'properties', 'methods', 'constants']);
});
