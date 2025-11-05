<?php

declare(strict_types=1);

namespace Aurora\Reflection\Cache;

use Aurora\Reflection\Exceptions\ReflectionReadException;
use Aurora\Reflection\Reader;
use Aurora\Reflection\VOs\Metadata;
use ReflectionException;

/**
 * Cached Reader using Decorator Pattern
 *
 * Wraps the standard Reader and caches reflection metadata to improve performance.
 * Use this when reading the same classes multiple times.
 *
 * Example usage:
 * ```php
 * $cache = new ArrayCache();
 * $cachedReader = new CachedReader(new Reader(), $cache);
 *
 * // First call - reads from reflection and stores in cache
 * $metadata = $cachedReader->read(MyClass::class);
 *
 * // Second call - returns from cache (much faster)
 * $metadata = $cachedReader->read(MyClass::class);
 * ```
 *
 * @template T of object
 */
final class CachedReader
{
    private const CACHE_PREFIX = 'aurora.reflection.';

    /**
     * @param  Reader<T>  $reader  The underlying reader
     * @param  CacheInterface  $cache  PSR-16 compatible cache
     * @param  null|int  $ttl  Time to live in seconds (null = forever)
     */
    public function __construct(
        private readonly Reader $reader,
        private readonly CacheInterface $cache,
        private readonly ?int $ttl = null,
    ) {}

    /**
     * Read class metadata with caching
     *
     * @param  class-string<T>  $class
     *
     * @throws ReflectionReadException|ReflectionException
     */
    public function read(string $class): Metadata
    {
        $key = $this->getCacheKey($class);

        // Try to get from cache
        $cached = $this->cache->get($key);
        if ($cached instanceof Metadata) {
            return $cached;
        }

        // Cache miss - read from reflection
        $metadata = $this->reader->read($class);

        // Store in cache
        $this->cache->set($key, $metadata, $this->ttl);

        return $metadata;
    }

    /**
     * Clear cached metadata for a specific class
     *
     * @param  class-string<T>  $class
     * @return bool True on success
     */
    public function forget(string $class): bool
    {
        $key = $this->getCacheKey($class);

        return $this->cache->delete($key);
    }

    /**
     * Clear all cached metadata
     *
     * @return bool True on success
     */
    public function flush(): bool
    {
        return $this->cache->clear();
    }

    /**
     * Check if metadata for a class is cached
     *
     * @param  class-string<T>  $class
     * @return bool True if cached
     */
    public function has(string $class): bool
    {
        $key = $this->getCacheKey($class);

        return $this->cache->has($key);
    }

    /**
     * Generate cache key for a class
     *
     * @param  class-string<T>  $class
     * @return string The cache key
     */
    private function getCacheKey(string $class): string
    {
        return self::CACHE_PREFIX.str_replace('\\', '.', $class);
    }
}
