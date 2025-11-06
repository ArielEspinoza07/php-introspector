# Type System

Aurora Reflection provides comprehensive support for PHP's type system, including union types, intersection types, nullable types, and special types.

## Type Metadata

Every property, parameter, method return type, and constant can have type information:

```php
$property->type;          // TypeMetadata|null
$parameter->type;         // TypeMetadata|null
$method->returnType;      // TypeMetadata|null
$constant->type;          // TypeMetadata|null
```

## TypeMetadata Structure

```php
TypeMetadata {
    +name: string                    // Type name
    +resolvedName: ?string           // Fully qualified name (for special types)
    +isBuiltin: bool                 // Is it a builtin type?
    +isNullable: bool                // Can it be null?
    +isUnion: bool                   // Is it a union type?
    +isIntersection: bool            // Is it an intersection type?
    +isSpecial: bool                 // Is it self/parent/static?
    +unionTypes: array               // Array of types if union
    +intersectionTypes: array        // Array of types if intersection
}
```

## Builtin Types

PHP builtin types are automatically detected:

```php
private string $name;
private int $age;
private float $price;
private bool $active;
private array $data;
private object $value;
private mixed $anything;
private null $nothing;
```

Access:

```php
foreach ($metadata->properties as $property) {
    if ($property->type?->isBuiltin) {
        echo "{$property->name} is a builtin type: {$property->type->name}";
    }
}
```

## Nullable Types

Types can be made nullable with `?` or `|null`:

```php
private ?string $description;
private string|null $notes;  // Same as above
```

Access:

```php
foreach ($metadata->properties as $property) {
    if ($property->type?->isNullable) {
        echo "{$property->name} can be null";
    }
}
```

## Union Types

Union types allow multiple possible types (PHP 8.0+):

```php
private string|int $identifier;
private User|Admin|Guest $actor;
private int|float $number;
```

Access:

```php
foreach ($metadata->properties as $property) {
    if ($property->type?->isUnion) {
        echo "{$property->name} is a union of: ";

        foreach ($property->type->unionTypes as $type) {
            echo $type->name . ', ';
        }
    }
}
```

Example:

```php
$metadata = $reader->read(MyClass::class);
$property = $metadata->properties['identifier'];

echo $property->type->isUnion;  // true
echo $property->type->name;     // "string|int"

foreach ($property->type->unionTypes as $type) {
    echo $type->name;           // "string", then "int"
    echo $type->isBuiltin;      // true, true
}
```

## Intersection Types

Intersection types require all types simultaneously (PHP 8.1+):

```php
public function process(Countable&Traversable $data): void
{
    // $data must implement BOTH interfaces
}
```

Access:

```php
foreach ($metadata->methods as $method) {
    foreach ($method->parameters as $param) {
        if ($param->type?->isIntersection) {
            echo "{$param->name} must implement: ";

            foreach ($param->type->intersectionTypes as $type) {
                echo $type->name . ' AND ';
            }
        }
    }
}
```

## Special Types

PHP has special types that reference the class itself:

### self

References the class where it's defined:

```php
class User
{
    public function clone(): self
    {
        return new self();
    }
}
```

Access:

```php
$method = $metadata->methods['clone'];

echo $method->returnType->name;         // "self"
echo $method->returnType->isSpecial;    // true
echo $method->returnType->resolvedName; // "App\User" (fully qualified)
```

### static

References the called class (late static binding):

```php
class User
{
    public static function create(): static
    {
        return new static();
    }
}
```

Access:

```php
$method = $metadata->methods['create'];

echo $method->returnType->name;         // "static"
echo $method->returnType->isSpecial;    // true
echo $method->returnType->resolvedName; // "App\User"
```

### parent

References the parent class:

```php
class Admin extends User
{
    public function getParent(): parent
    {
        // ...
    }
}
```

## Generic/Template Types (DocBlock)

While PHP doesn't support generic types natively, they're commonly used in DocBlocks:

```php
/**
 * @var array<string, mixed>
 */
private array $config;

/**
 * @var list<User>
 */
private array $users;

/**
 * @param Collection<int, array<string, mixed>> $items
 */
public function process(Collection $items): void
```

These are parsed from DocBlocks:

```php
$property = $metadata->properties['config'];
$varType = $property->docBlock?->var?->type;

echo $varType; // "array<string, mixed>"
```

## Class Types

When a type is a class, you get the full class name:

```php
private User $user;
private DateTimeImmutable $createdAt;
```

Access:

```php
foreach ($metadata->properties as $property) {
    if ($property->type && !$property->type->isBuiltin) {
        echo "{$property->name} is of class: {$property->type->name}";

        // Check if it's a specific class
        if ($property->type->name === User::class) {
            echo "This is a User property";
        }
    }
}
```

## Practical Examples

### Example 1: Type Validation

```php
function validateTypes(string $className): array
{
    $reader = new Reader();
    $metadata = $reader->read($className);
    $issues = [];

    foreach ($metadata->properties as $property) {
        // Ensure all properties have types
        if ($property->type === null) {
            $issues[] = "Property {$property->name} has no type";
        }
    }

    foreach ($metadata->methods as $method) {
        // Ensure all public methods have return types
        if ($method->modifier->visibility === Visibility::Public &&
            $method->returnType === null) {
            $issues[] = "Method {$method->name} has no return type";
        }

        // Ensure all parameters have types
        foreach ($method->parameters as $param) {
            if ($param->type === null) {
                $issues[] = "Parameter \${$param->name} in {$method->name} has no type";
            }
        }
    }

    return $issues;
}
```

### Example 2: Find Nullable Properties

```php
function findNullableProperties(string $className): array
{
    $reader = new Reader();
    $metadata = $reader->read($className);
    $nullable = [];

    foreach ($metadata->properties as $property) {
        if ($property->type?->isNullable) {
            $nullable[] = $property->name;
        }
    }

    return $nullable;
}

$nullable = findNullableProperties(User::class);
echo "Nullable properties: " . implode(', ', $nullable);
```

### Example 3: Type Summary

```php
function generateTypeSummary(string $className): array
{
    $reader = new Reader();
    $metadata = $reader->read($className);

    $summary = [
        'builtin' => 0,
        'classes' => 0,
        'nullable' => 0,
        'union' => 0,
        'intersection' => 0,
    ];

    foreach ($metadata->properties as $property) {
        if ($property->type === null) {
            continue;
        }

        if ($property->type->isBuiltin) {
            $summary['builtin']++;
        } else {
            $summary['classes']++;
        }

        if ($property->type->isNullable) {
            $summary['nullable']++;
        }

        if ($property->type->isUnion) {
            $summary['union']++;
        }

        if ($property->type->isIntersection) {
            $summary['intersection']++;
        }
    }

    return $summary;
}

$summary = generateTypeSummary(User::class);
print_r($summary);
```

### Example 4: Resolve Special Types

```php
function resolveTypes(string $className): void
{
    $reader = new Reader();
    $metadata = $reader->read($className);

    foreach ($metadata->methods as $method) {
        if ($method->returnType?->isSpecial) {
            echo "Method {$method->name} returns: {$method->returnType->name}\n";
            echo "  Resolved to: {$method->returnType->resolvedName}\n";
        }
    }
}

resolveTypes(User::class);
// Output:
// Method clone returns: self
//   Resolved to: App\User
// Method create returns: static
//   Resolved to: App\User
```

## Type Comparison

```php
// Check for specific types
if ($property->type?->name === 'string') {
    echo "This is a string property";
}

// Check for class types
if ($property->type?->name === User::class) {
    echo "This is a User property";
}

// Check for nullable types
if ($property->type?->isNullable && $property->type->name === 'string') {
    echo "This is a nullable string";
}

// Use resolved name for special types
if ($method->returnType?->resolvedName === User::class) {
    echo "This method returns User (could be self, static, or User)";
}
```

## Related Documentation

- [Core Concepts](core-concepts.md) - Metadata structure
- [DocBlocks](docblocks.md) - DocBlock type hints
- [Best Practices](best-practices.md) - Type-safe access patterns
