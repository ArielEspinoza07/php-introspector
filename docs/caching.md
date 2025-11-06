# Caching

Reflection is expensive. Use caching in production for optimal performance.

## Why Cache?

Reflection operations are slow because they:
- Parse PHP files
- Extract metadata
- Parse DocBlocks
- Analyze types

**Without cache:** ~5-15ms per class
**With cache:** ~0.05-0.1ms per class (**100x faster**)

## Array Cache (In-Memory)

The simplest cache for single requests:

```php
use Aurora\Reflection\Reader;
use Aurora\Reflection\Cache\CachedReader;
use Aurora\Reflection\Cache\ArrayCache;

$cache = new ArrayCache();
$cachedReader = new CachedReader(new Reader(), $cache);

// First call - reads from reflection (slow)
$metadata = $cachedReader->read(User::class);

// Second call - returns from cache (fast!)
$metadata = $cachedReader->read(User::class);
```

**Note:** `ArrayCache` stores data in memory and is cleared at the end of the request.

## PSR-16 Compatible Cache

Use any PSR-16 cache implementation for persistent caching:

### Symfony Cache

```php
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Psr16Cache;
use Aurora\Reflection\Cache\CachedReader;
use Aurora\Reflection\Reader;

$adapter = new FilesystemAdapter();
$psr16Cache = new Psr16Cache($adapter);

$cachedReader = new CachedReader(new Reader(), $psr16Cache);

$metadata = $cachedReader->read(User::class);
```

### Laravel Cache

```php
use Illuminate\Support\Facades\Cache;
use Aurora\Reflection\Cache\CachedReader;
use Aurora\Reflection\Reader;

$cachedReader = new CachedReader(
    new Reader(),
    Cache::store('redis')
);

$metadata = $cachedReader->read(User::class);
```

## Time-To-Live (TTL)

Set cache expiration:

```php
// Cache for 1 hour (3600 seconds)
$cachedReader = new CachedReader(new Reader(), $cache, ttl: 3600);

// Cache for 24 hours
$cachedReader = new CachedReader(new Reader(), $cache, ttl: 86400);

// Cache forever (null)
$cachedReader = new CachedReader(new Reader(), $cache, ttl: null);
```

## Cache Management

### Check if Cached

```php
if ($cachedReader->has(User::class)) {
    echo 'Metadata is cached';
} else {
    echo 'Will read from reflection';
}
```

### Invalidate Cache

```php
// Forget specific class
$cachedReader->forget(User::class);

// Clear all cache
$cachedReader->flush();
```

## Cache Warming

Pre-populate the cache during deployment:

```php
use Aurora\Reflection\Reader;
use Aurora\Reflection\Cache\CachedReader;

function warmCache(CachedReader $reader, array $classes): void
{
    foreach ($classes as $class) {
        echo "Warming cache for {$class}...\n";
        $reader->read($class);
    }

    echo "Cache warmed!\n";
}

$cachedReader = new CachedReader(new Reader(), $cache);

$classes = [
    User::class,
    Post::class,
    Comment::class,
    // ... all your classes
];

warmCache($cachedReader, $classes);
```

## Production Setup

### Option 1: File Cache

```php
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Psr16Cache;
use Aurora\Reflection\Cache\CachedReader;
use Aurora\Reflection\Reader;

// Store cache in var/cache/reflection
$adapter = new FilesystemAdapter(
    namespace: 'reflection',
    defaultLifetime: 0, // Never expire
    directory: __DIR__ . '/var/cache/reflection'
);

$psr16Cache = new Psr16Cache($adapter);
$reader = new CachedReader(new Reader(), $psr16Cache);

return $reader;
```

### Option 2: Redis Cache

```php
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Cache\Psr16Cache;
use Aurora\Reflection\Cache\CachedReader;
use Aurora\Reflection\Reader;

$redis = new \Redis();
$redis->connect('127.0.0.1', 6379);

$adapter = new RedisAdapter($redis, namespace: 'reflection');
$psr16Cache = new Psr16Cache($adapter);

$reader = new CachedReader(new Reader(), $psr16Cache);

return $reader;
```

### Option 3: APCu Cache

```php
use Symfony\Component\Cache\Adapter\ApcuAdapter;
use Symfony\Component\Cache\Psr16Cache;
use Aurora\Reflection\Cache\CachedReader;
use Aurora\Reflection\Reader;

$adapter = new ApcuAdapter(namespace: 'reflection');
$psr16Cache = new Psr16Cache($adapter);

$reader = new CachedReader(new Reader(), $psr16Cache);

return $reader;
```

## Environment-Based Caching

```php
use Aurora\Reflection\Reader;
use Aurora\Reflection\Cache\CachedReader;
use Aurora\Reflection\Cache\ArrayCache;

function createReader(string $environment): Reader|CachedReader
{
    $reader = new Reader();

    // Only cache in production
    if ($environment === 'production') {
        $cache = new ArrayCache();
        return new CachedReader($reader, $cache);
    }

    return $reader;
}

$reader = createReader($_ENV['APP_ENV'] ?? 'development');
```

## Cache Invalidation Strategy

### Strategy 1: Clear on Deployment

```bash
# In your deployment script
php bin/console cache:clear
```

### Strategy 2: Version-Based Cache

```php
$version = '1.0.0'; // Update on each deploy

$adapter = new FilesystemAdapter(
    namespace: "reflection_{$version}"
);
```

### Strategy 3: File-Based Invalidation

```php
// Only cache if file hasn't changed
function getCachedMetadata(
    CachedReader $reader,
    string $className
): Metadata {
    $cacheKey = str_replace('\\', '.', $className);
    $filePath = (new ReflectionClass($className))->getFileName();
    $fileTime = filemtime($filePath);

    // Store file modification time with cache
    $cacheData = $reader->cache->get($cacheKey . '.time');

    if ($cacheData !== $fileTime) {
        $reader->forget($className);
        $reader->cache->set($cacheKey . '.time', $fileTime);
    }

    return $reader->read($className);
}
```

## Best Practices

✅ **Always cache in production**
✅ **Use persistent cache (Redis, File)**
✅ **Warm cache on deployment**
✅ **Clear cache on code changes**
✅ **Monitor cache hit rates**

❌ **Don't use ArrayCache in production**
❌ **Don't cache in development**
❌ **Don't forget to invalidate**

## Benchmarks

Real-world performance comparison:

```php
use Aurora\Reflection\Reader;
use Aurora\Reflection\Cache\CachedReader;
use Aurora\Reflection\Cache\ArrayCache;

$reader = new Reader();
$cachedReader = new CachedReader($reader, new ArrayCache());

// Without cache
$start = microtime(true);
for ($i = 0; $i < 100; $i++) {
    $reader->read(User::class);
}
$time = microtime(true) - $start;
echo "Without cache: {$time}s\n"; // ~0.8s

// With cache (after first read)
$cachedReader->read(User::class); // Prime cache
$start = microtime(true);
for ($i = 0; $i < 100; $i++) {
    $cachedReader->read(User::class);
}
$time = microtime(true) - $start;
echo "With cache: {$time}s\n"; // ~0.008s
```

**Result: 100x faster!**

## Related Documentation

- [Installation](installation.md) - Setup guide
- [Best Practices](best-practices.md) - Performance tips
