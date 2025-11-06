# Best Practices

Guidelines for using Aurora Reflection effectively and efficiently.

## 1. Always Use Caching in Production

**❌ Bad:**
```php
$reader = new Reader();
$metadata = $reader->read(User::class); // Slow every time
```

**✅ Good:**
```php
$cachedReader = new CachedReader(
    new Reader(),
    $cache,
    ttl: 3600
);
$metadata = $cachedReader->read(User::class); // Fast after first read
```

**Why:** Reflection is expensive. Caching gives 100x performance improvement.

## 2. Use Type-Safe Access

**❌ Bad:**
```php
if ($property->type->name === 'string') {
    // Might not handle special types correctly
}
```

**✅ Good:**
```php
if ($property->type?->resolvedName === User::class) {
    // Handles both "User" and "self" return types
}
```

**Why:** Special types (`self`, `parent`, `static`) need resolution.

## 3. Handle Optional Values

**❌ Bad:**
```php
echo $method->docBlock->summary; // Might be null!
```

**✅ Good:**
```php
echo $method->docBlock?->summary ?? 'No description';
```

**Why:** Not all members have DocBlocks or types.

## 4. Don't Reflect in Hot Paths

**❌ Bad:**
```php
foreach ($users as $user) {
    $metadata = $reader->read($user::class); // Reflects 1000 times!
}
```

**✅ Good:**
```php
$metadata = $reader->read(User::class); // Reflect once
foreach ($users as $user) {
    // Use $metadata
}
```

**Why:** Reflection is slow. Do it once, reuse the result.

## 5. Warm Cache on Deployment

**✅ Good:**
```php
// deploy.php
$classes = [User::class, Post::class, Comment::class];
foreach ($classes as $class) {
    $cachedReader->read($class);
}
```

**Why:** Ensures first request is fast.

## 6. Use Specific Visibility Checks

**❌ Bad:**
```php
if ($property->modifier->visibility->value === 'public') {
    // String comparison
}
```

**✅ Good:**
```php
use Aurora\Reflection\Enums\Visibility;

if ($property->modifier->visibility === Visibility::Public) {
    // Type-safe enum comparison
}
```

**Why:** Type safety, autocompletion, refactoring support.

## 7. Filter Members Efficiently

**❌ Bad:**
```php
$publicMethods = [];
foreach ($metadata->methods as $method) {
    if ($method->modifier->visibility === Visibility::Public) {
        $publicMethods[] = $method;
    }
}
```

**✅ Good:**
```php
$publicMethods = array_filter(
    $metadata->properties,
    fn($m) => $m->modifier->visibility === Visibility::Public
);
```

**Why:** More concise and functional.

## 8. Use Member Source Tracking

**✅ Good:**
```php
use Aurora\Reflection\Enums\SourceType;

$traitMethods = array_filter(
    $metadata->methods,
    fn($m) => $m->declaringSource->type === SourceType::Trait_
);
```

**Why:** Know exactly where each member comes from.

## 9. Validate DocBlock Completeness

**✅ Good:**
```php
foreach ($metadata->methods as $method) {
    if ($method->modifier->visibility === Visibility::Public) {
        if ($method->docBlock === null) {
            trigger_error("Method {$method->name} lacks documentation");
        }
    }
}
```

**Why:** Maintain code quality and documentation standards.

## 10. Use Environment-Based Caching

**✅ Good:**
```php
function createReader(string $env): Reader|CachedReader
{
    $reader = new Reader();

    if ($env === 'production') {
        return new CachedReader($reader, $cache);
    }

    return $reader;
}

$reader = createReader($_ENV['APP_ENV']);
```

**Why:** Fast in production, accurate in development.

## Performance Tips

### Use Persistent Cache

**❌ ArrayCache in production:**
```php
$cache = new ArrayCache(); // Cleared after request
```

**✅ Redis/File cache in production:**
```php
$cache = new RedisCache(); // Persists across requests
```

### Batch Processing

When processing multiple classes:

```php
$classes = [User::class, Post::class, Comment::class];
$metadataMap = [];

foreach ($classes as $class) {
    $metadataMap[$class] = $reader->read($class);
}

// Now process all metadata
foreach ($metadataMap as $class => $metadata) {
    // ...
}
```

### Lazy Loading

Only read metadata when actually needed:

```php
class MetadataRegistry
{
    private array $cache = [];

    public function get(string $class): Metadata
    {
        return $this->cache[$class] ??= $this->reader->read($class);
    }
}
```

## Code Quality

### Validate Types

Ensure all properties have types:

```php
foreach ($metadata->properties as $property) {
    if ($property->type === null) {
        throw new RuntimeException(
            "Property {$property->name} has no type"
        );
    }
}
```

### Check for Deprecations

```php
foreach ($metadata->methods as $method) {
    foreach ($method->docBlock?->custom ?? [] as $tag) {
        if ($tag->type === 'deprecated') {
            trigger_error(
                "Method {$method->name} is deprecated: {$tag->description}",
                E_USER_DEPRECATED
            );
        }
    }
}
```

## Testing

### Mock Reader for Tests

```php
// In tests, use a mock reader
$mockReader = $this->createMock(Reader::class);
$mockReader->method('read')
    ->willReturn($mockMetadata);

$service = new MyService($mockReader);
```

### Test with Real Classes

```php
class ReflectionTest extends TestCase
{
    public function testUserMetadata(): void
    {
        $reader = new Reader();
        $metadata = $reader->read(User::class);

        $this->assertCount(5, $metadata->properties);
        $this->assertTrue($metadata->class->modifier->isFinal);
    }
}
```

## Common Patterns

### Property Mapper

```php
function mapFromMetadata(Metadata $metadata, array $data): object
{
    $instance = new ($metadata->class->name)();

    foreach ($metadata->properties as $property) {
        if (isset($data[$property->name])) {
            $property->setValue($instance, $data[$property->name]);
        }
    }

    return $instance;
}
```

### Validation Rules Generator

```php
function generateValidation(Metadata $metadata): array
{
    $rules = [];

    foreach ($metadata->properties as $property) {
        $rules[$property->name] = [];

        if (!$property->type?->isNullable) {
            $rules[$property->name][] = 'required';
        }

        if ($property->type?->name === 'string') {
            $rules[$property->name][] = 'string';
        }
    }

    return $rules;
}
```

## What to Avoid

❌ Don't use reflection for runtime type checking (use actual type hints)
❌ Don't reflect the same class multiple times
❌ Don't use reflection in loops
❌ Don't skip caching in production
❌ Don't ignore null checks
❌ Don't use string comparisons for enums

## Checklist

Before deploying:

- [ ] Caching enabled in production
- [ ] Cache warmed during deployment
- [ ] All null checks in place
- [ ] Using type-safe comparisons
- [ ] Not reflecting in hot paths
- [ ] Environment-based configuration
- [ ] Monitoring cache hit rates

## Related Documentation

- [Caching](caching.md) - Detailed caching guide
- [Core Concepts](core-concepts.md) - Understanding the API
- [Member Source Tracking](member-source-tracking.md) - Track member origins
