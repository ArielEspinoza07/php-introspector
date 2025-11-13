<?php

declare(strict_types=1);

use Introspector\Cache\ArrayCache;
use Introspector\Cache\CachedReader;
use Introspector\Reader;
use Introspector\Tests\Fixtures\Circle;
use Introspector\Tests\Fixtures\CompleteClass;

test('caches metadata on first read', function () {
    $cache = new ArrayCache();
    $cachedReader = new CachedReader(new Reader(), $cache);

    expect($cachedReader->has(CompleteClass::class))->toBeFalse();

    $metadata = $cachedReader->read(CompleteClass::class);

    expect($metadata->class->name)->toBe(CompleteClass::class)
        ->and($cachedReader->has(CompleteClass::class))->toBeTrue();
});

test('returns cached metadata on subsequent reads', function () {
    $cache = new ArrayCache();
    $reader = new Reader();
    $cachedReader = new CachedReader($reader, $cache);

    // First read - populates cache
    $firstRead = $cachedReader->read(CompleteClass::class);

    // Second read - should return from cache (same instance)
    $secondRead = $cachedReader->read(CompleteClass::class);

    expect($firstRead)->toBe($secondRead)
        ->and($firstRead->class->name)->toBe(CompleteClass::class);
});

test('caches different classes independently', function () {
    $cache = new ArrayCache();
    $cachedReader = new CachedReader(new Reader(), $cache);

    $completeClassMetadata = $cachedReader->read(CompleteClass::class);
    $circleMetadata = $cachedReader->read(Circle::class);

    expect($completeClassMetadata->class->name)->toBe(CompleteClass::class)
        ->and($circleMetadata->class->name)->toBe(Circle::class)
        ->and($cachedReader->has(CompleteClass::class))->toBeTrue()
        ->and($cachedReader->has(Circle::class))->toBeTrue();
});

test('can forget specific class from cache', function () {
    $cache = new ArrayCache();
    $cachedReader = new CachedReader(new Reader(), $cache);

    // Read and cache
    $cachedReader->read(CompleteClass::class);
    expect($cachedReader->has(CompleteClass::class))->toBeTrue();

    // Forget
    $result = $cachedReader->forget(CompleteClass::class);

    expect($result)->toBeTrue()
        ->and($cachedReader->has(CompleteClass::class))->toBeFalse();
});

test('forget returns true even if class was not cached', function () {
    $cache = new ArrayCache();
    $cachedReader = new CachedReader(new Reader(), $cache);

    $result = $cachedReader->forget(CompleteClass::class);

    expect($result)->toBeTrue();
});

test('can flush entire cache', function () {
    $cache = new ArrayCache();
    $cachedReader = new CachedReader(new Reader(), $cache);

    // Cache multiple classes
    $cachedReader->read(CompleteClass::class);
    $cachedReader->read(Circle::class);

    expect($cachedReader->has(CompleteClass::class))->toBeTrue()
        ->and($cachedReader->has(Circle::class))->toBeTrue();

    // Flush
    $result = $cachedReader->flush();

    expect($result)->toBeTrue()
        ->and($cachedReader->has(CompleteClass::class))->toBeFalse()
        ->and($cachedReader->has(Circle::class))->toBeFalse();
});

test('respects TTL when provided', function () {
    $cache = new ArrayCache();
    $cachedReader = new CachedReader(new Reader(), $cache, ttl: 1); // 1 second TTL

    // Read and cache
    $cachedReader->read(CompleteClass::class);
    expect($cachedReader->has(CompleteClass::class))->toBeTrue();

    // Wait for expiration
    sleep(2);

    // Should be expired
    expect($cachedReader->has(CompleteClass::class))->toBeFalse();
});

test('cache key uses class namespace correctly', function () {
    $cache = new ArrayCache();
    $cachedReader = new CachedReader(new Reader(), $cache);

    // Read a class with namespace
    $cachedReader->read(CompleteClass::class);

    // The cache should have the key with dots instead of backslashes
    // We can verify by checking has()
    expect($cachedReader->has('Introspector\Tests\Fixtures\CompleteClass'))->toBeTrue();
});

test('reading after forget re-reads from reflection', function () {
    $cache = new ArrayCache();
    $cachedReader = new CachedReader(new Reader(), $cache);

    // First read
    $firstMetadata = $cachedReader->read(CompleteClass::class);

    // Forget
    $cachedReader->forget(CompleteClass::class);

    // Second read - should re-read from reflection
    $secondMetadata = $cachedReader->read(CompleteClass::class);

    // Should be different instances but same data
    expect($firstMetadata)->not->toBe($secondMetadata)
        ->and($firstMetadata->class->name)->toBe($secondMetadata->class->name)
        ->and($firstMetadata->class->name)->toBe(CompleteClass::class);
});

test('can cache without TTL for indefinite storage', function () {
    $cache = new ArrayCache();
    $cachedReader = new CachedReader(new Reader(), $cache, ttl: null);

    $cachedReader->read(CompleteClass::class);

    // Should remain cached (we can't test indefinitely, but check it's still there)
    expect($cachedReader->has(CompleteClass::class))->toBeTrue();
});

test('cached metadata is fully functional', function () {
    $cache = new ArrayCache();
    $cachedReader = new CachedReader(new Reader(), $cache);

    // First read - from reflection
    $cachedReader->read(CompleteClass::class);

    // Second read - from cache
    $metadata = $cachedReader->read(CompleteClass::class);

    // Verify cached metadata has all properties intact
    expect($metadata->class->name)->toBe(CompleteClass::class)
        ->and($metadata->properties)->not->toBeEmpty()
        ->and($metadata->methods)->not->toBeEmpty()
        ->and($metadata->constants)->not->toBeEmpty()
        ->and($metadata->constructor)->not->toBeNull();
});

test('multiple CachedReader instances can share same cache', function () {
    $sharedCache = new ArrayCache();

    $reader1 = new CachedReader(new Reader(), $sharedCache);
    $reader2 = new CachedReader(new Reader(), $sharedCache);

    // First reader caches
    $reader1->read(CompleteClass::class);

    // Second reader should find it in cache
    expect($reader2->has(CompleteClass::class))->toBeTrue();

    $metadata = $reader2->read(CompleteClass::class);
    expect($metadata->class->name)->toBe(CompleteClass::class);
});

test('forgetting from one reader affects other readers sharing cache', function () {
    $sharedCache = new ArrayCache();

    $reader1 = new CachedReader(new Reader(), $sharedCache);
    $reader2 = new CachedReader(new Reader(), $sharedCache);

    // First reader caches
    $reader1->read(CompleteClass::class);
    expect($reader2->has(CompleteClass::class))->toBeTrue();

    // First reader forgets
    $reader1->forget(CompleteClass::class);

    // Second reader should not find it
    expect($reader2->has(CompleteClass::class))->toBeFalse();
});

test('flushing from one reader affects other readers sharing cache', function () {
    $sharedCache = new ArrayCache();

    $reader1 = new CachedReader(new Reader(), $sharedCache);
    $reader2 = new CachedReader(new Reader(), $sharedCache);

    // Cache multiple classes
    $reader1->read(CompleteClass::class);
    $reader2->read(Circle::class);

    expect($reader1->has(Circle::class))->toBeTrue()
        ->and($reader2->has(CompleteClass::class))->toBeTrue();

    // Flush from reader1
    $reader1->flush();

    // Both classes should be gone from both readers
    expect($reader1->has(CompleteClass::class))->toBeFalse()
        ->and($reader1->has(Circle::class))->toBeFalse()
        ->and($reader2->has(CompleteClass::class))->toBeFalse()
        ->and($reader2->has(Circle::class))->toBeFalse();
});
