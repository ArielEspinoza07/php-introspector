<?php

declare(strict_types=1);

use Introspector\Cache\ArrayCache;

test('can set and get value', function () {
    $cache = new ArrayCache();

    $result = $cache->set('key', 'value');

    expect($result)->toBeTrue()
        ->and($cache->get('key'))->toBe('value');
});

test('returns default when key does not exist', function () {
    $cache = new ArrayCache();

    $result = $cache->get('nonexistent', 'default');

    expect($result)->toBe('default');
});

test('returns null as default when key does not exist', function () {
    $cache = new ArrayCache();

    $result = $cache->get('nonexistent');

    expect($result)->toBeNull();
});

test('can store different data types', function () {
    $cache = new ArrayCache();

    $cache->set('string', 'value');
    $cache->set('int', 42);
    $cache->set('float', 3.14);
    $cache->set('bool', true);
    $cache->set('array', ['a', 'b', 'c']);
    $cache->set('object', (object) ['prop' => 'value']);

    expect($cache->get('string'))->toBe('value')
        ->and($cache->get('int'))->toBe(42)
        ->and($cache->get('float'))->toBe(3.14)
        ->and($cache->get('bool'))->toBeTrue()
        ->and($cache->get('array'))->toBe(['a', 'b', 'c'])
        ->and($cache->get('object'))->toBeObject();
});

test('can check if key exists', function () {
    $cache = new ArrayCache();

    $cache->set('key', 'value');

    expect($cache->has('key'))->toBeTrue()
        ->and($cache->has('nonexistent'))->toBeFalse();
});

test('can delete value', function () {
    $cache = new ArrayCache();

    $cache->set('key', 'value');
    expect($cache->has('key'))->toBeTrue();

    $result = $cache->delete('key');

    expect($result)->toBeTrue()
        ->and($cache->has('key'))->toBeFalse()
        ->and($cache->get('key'))->toBeNull();
});

test('delete returns true even if key does not exist', function () {
    $cache = new ArrayCache();

    $result = $cache->delete('nonexistent');

    expect($result)->toBeTrue();
});

test('can clear all values', function () {
    $cache = new ArrayCache();

    $cache->set('key1', 'value1');
    $cache->set('key2', 'value2');
    $cache->set('key3', 'value3');

    $result = $cache->clear();

    expect($result)->toBeTrue()
        ->and($cache->has('key1'))->toBeFalse()
        ->and($cache->has('key2'))->toBeFalse()
        ->and($cache->has('key3'))->toBeFalse();
});

test('supports TTL with integer seconds', function () {
    $cache = new ArrayCache();

    $cache->set('key', 'value', 1); // 1 second TTL

    expect($cache->has('key'))->toBeTrue()
        ->and($cache->get('key'))->toBe('value');

    // Wait for expiration
    sleep(2);

    expect($cache->has('key'))->toBeFalse()
        ->and($cache->get('key'))->toBeNull();
});

test('supports TTL with DateInterval', function () {
    $cache = new ArrayCache();

    $ttl = new DateInterval('PT1S'); // 1 second
    $cache->set('key', 'value', $ttl);

    expect($cache->has('key'))->toBeTrue();

    // Wait for expiration
    sleep(2);

    expect($cache->has('key'))->toBeFalse();
});

test('null TTL means no expiration', function () {
    $cache = new ArrayCache();

    $cache->set('key', 'value', null);

    expect($cache->has('key'))->toBeTrue()
        ->and($cache->get('key'))->toBe('value');

    // Even after some time, it should still be there
    sleep(1);
    expect($cache->has('key'))->toBeTrue();
});

test('updating value resets expiration', function () {
    $cache = new ArrayCache();

    // Set with short TTL
    $cache->set('key', 'value1', 1);
    sleep(1);

    // Update with new TTL before expiration
    $cache->set('key', 'value2', 5);

    // Should still exist with new value
    expect($cache->has('key'))->toBeTrue()
        ->and($cache->get('key'))->toBe('value2');
});

test('can get multiple values', function () {
    $cache = new ArrayCache();

    $cache->set('key1', 'value1');
    $cache->set('key2', 'value2');
    $cache->set('key3', 'value3');

    $result = $cache->getMultiple(['key1', 'key2', 'key3']);

    expect($result)->toBe([
        'key1' => 'value1',
        'key2' => 'value2',
        'key3' => 'value3',
    ]);
});

test('getMultiple returns default for missing keys', function () {
    $cache = new ArrayCache();

    $cache->set('key1', 'value1');

    $result = $cache->getMultiple(['key1', 'key2', 'key3'], 'default');

    expect($result)->toBe([
        'key1' => 'value1',
        'key2' => 'default',
        'key3' => 'default',
    ]);
});

test('can set multiple values', function () {
    $cache = new ArrayCache();

    $result = $cache->setMultiple([
        'key1' => 'value1',
        'key2' => 'value2',
        'key3' => 'value3',
    ]);

    expect($result)->toBeTrue()
        ->and($cache->get('key1'))->toBe('value1')
        ->and($cache->get('key2'))->toBe('value2')
        ->and($cache->get('key3'))->toBe('value3');
});

test('setMultiple respects TTL', function () {
    $cache = new ArrayCache();

    $cache->setMultiple([
        'key1' => 'value1',
        'key2' => 'value2',
    ], 1); // 1 second TTL

    expect($cache->has('key1'))->toBeTrue()
        ->and($cache->has('key2'))->toBeTrue();

    sleep(2);

    expect($cache->has('key1'))->toBeFalse()
        ->and($cache->has('key2'))->toBeFalse();
});

test('can delete multiple values', function () {
    $cache = new ArrayCache();

    $cache->set('key1', 'value1');
    $cache->set('key2', 'value2');
    $cache->set('key3', 'value3');

    $result = $cache->deleteMultiple(['key1', 'key3']);

    expect($result)->toBeTrue()
        ->and($cache->has('key1'))->toBeFalse()
        ->and($cache->has('key2'))->toBeTrue()
        ->and($cache->has('key3'))->toBeFalse();
});

test('deleteMultiple returns true even if keys do not exist', function () {
    $cache = new ArrayCache();

    $result = $cache->deleteMultiple(['nonexistent1', 'nonexistent2']);

    expect($result)->toBeTrue();
});

test('expired values are automatically deleted on has check', function () {
    $cache = new ArrayCache();

    $cache->set('key', 'value', 1);

    // Initially exists
    expect($cache->has('key'))->toBeTrue();

    sleep(2);

    // has() should delete expired entry
    expect($cache->has('key'))->toBeFalse();

    // After deletion, get should return default
    expect($cache->get('key', 'default'))->toBe('default');
});

test('can overwrite existing value', function () {
    $cache = new ArrayCache();

    $cache->set('key', 'value1');
    expect($cache->get('key'))->toBe('value1');

    $cache->set('key', 'value2');
    expect($cache->get('key'))->toBe('value2');
});

test('handles empty string as value', function () {
    $cache = new ArrayCache();

    $cache->set('key', '');

    expect($cache->has('key'))->toBeTrue()
        ->and($cache->get('key'))->toBe('');
});

test('handles zero as value', function () {
    $cache = new ArrayCache();

    $cache->set('key', 0);

    expect($cache->has('key'))->toBeTrue()
        ->and($cache->get('key'))->toBe(0);
});

test('handles false as value', function () {
    $cache = new ArrayCache();

    $cache->set('key', false);

    expect($cache->has('key'))->toBeTrue()
        ->and($cache->get('key'))->toBe(false);
});

test('handles null as value', function () {
    $cache = new ArrayCache();

    $cache->set('key', null);

    // Note: PSR-16 behavior - setting null is valid
    expect($cache->has('key'))->toBeTrue()
        ->and($cache->get('key'))->toBeNull();
});

test('clear removes all expirations', function () {
    $cache = new ArrayCache();

    $cache->set('key1', 'value1', 100);
    $cache->set('key2', 'value2', 200);

    $cache->clear();

    // After clear, even if we set the same keys again without TTL, they should work fine
    $cache->set('key1', 'new_value');

    expect($cache->get('key1'))->toBe('new_value')
        ->and($cache->has('key1'))->toBeTrue();
});

test('delete removes expiration along with value', function () {
    $cache = new ArrayCache();

    $cache->set('key', 'value', 100);
    $cache->delete('key');

    // Set same key without TTL
    $cache->set('key', 'new_value');

    // Should work fine without old expiration interfering
    expect($cache->get('key'))->toBe('new_value');
});

test('supports large data storage', function () {
    $cache = new ArrayCache();

    $largeArray = array_fill(0, 1000, 'value');
    $cache->set('large', $largeArray);

    expect($cache->get('large'))->toBe($largeArray)
        ->and(count($cache->get('large')))->toBe(1000);
});

test('multiple operations maintain data integrity', function () {
    $cache = new ArrayCache();

    // Set multiple values
    $cache->set('a', 1);
    $cache->set('b', 2);
    $cache->set('c', 3);

    // Delete one
    $cache->delete('b');

    // Set another
    $cache->set('d', 4);

    // Update existing
    $cache->set('a', 10);

    expect($cache->get('a'))->toBe(10)
        ->and($cache->has('b'))->toBeFalse()
        ->and($cache->get('c'))->toBe(3)
        ->and($cache->get('d'))->toBe(4);
});
