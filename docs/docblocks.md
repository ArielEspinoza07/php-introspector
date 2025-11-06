# DocBlock Parsing

Aurora Reflection provides comprehensive DocBlock parsing for classes, properties, methods, and constants.

## Overview

DocBlocks are parsed automatically when reading metadata. The library extracts:
- Summary (first line)
- Description (detailed text)
- Standard tags (`@param`, `@return`, `@var`, `@throws`)
- Custom tags (`@author`, `@version`, `@deprecated`, etc.)

## Basic Structure

```php
/**
 * Creates a new user instance
 *
 * This method validates the email and generates a unique ID.
 *
 * @param string $name The user's full name
 * @param string $email The user's email address
 * @return self A new User instance
 * @throws InvalidArgumentException If email is invalid
 * @author John Doe
 * @version 1.0.0
 */
public function create(string $name, string $email): self
{
    // ...
}
```

## Accessing DocBlocks

### Method DocBlocks

```php
use Aurora\Reflection\Reader;

$reader = new Reader();
$metadata = $reader->read(User::class);

foreach ($metadata->methods as $method) {
    $docBlock = $method->docBlock;

    if ($docBlock !== null) {
        // Summary and description
        echo $docBlock->summary;      // "Creates a new user instance"
        echo $docBlock->description;  // Full description text

        // Parameters
        foreach ($docBlock->params as $param) {
            echo $param->name;        // "name", "email"
            echo $param->type;        // "string"
            echo $param->description; // "The user's full name"
        }

        // Return type
        if ($docBlock->return !== null) {
            echo $docBlock->return->type;        // "self"
            echo $docBlock->return->description; // "A new User instance"
        }

        // Exceptions
        foreach ($docBlock->throws as $throws) {
            echo $throws->type;        // "InvalidArgumentException"
            echo $throws->description; // "If email is invalid"
        }

        // Custom tags
        foreach ($docBlock->custom as $tag) {
            echo $tag->type;        // "author", "version"
            echo $tag->description; // "John Doe", "1.0.0"
        }
    }
}
```

### Property DocBlocks

```php
/**
 * The user's email address
 *
 * @var string
 */
private string $email;
```

Access:

```php
foreach ($metadata->properties as $property) {
    $docBlock = $property->docBlock;

    if ($docBlock !== null) {
        echo $docBlock->summary;           // "The user's email address"
        echo $docBlock->var?->type;        // "string"
        echo $docBlock->var?->description; // Additional description if any
    }
}
```

### Class DocBlocks

```php
/**
 * User model class
 *
 * Represents a user in the system with authentication
 * and profile management capabilities.
 *
 * @author John Doe
 * @version 1.0.0
 * @since 2024-01-01
 */
class User
{
    // ...
}
```

Access:

```php
$classDocBlock = $metadata->class->docBlock;

if ($classDocBlock !== null) {
    echo $classDocBlock->summary;     // "User model class"
    echo $classDocBlock->description; // Full description

    foreach ($classDocBlock->custom as $tag) {
        echo "{$tag->type}: {$tag->description}";
    }
}
```

### Constant DocBlocks

```php
/**
 * Maximum number of login attempts
 *
 * @var int
 */
public const MAX_LOGIN_ATTEMPTS = 3;
```

Access:

```php
foreach ($metadata->constants as $constant) {
    $docBlock = $constant->docBlock;

    if ($docBlock !== null) {
        echo $docBlock->summary; // "Maximum number of login attempts"
    }
}
```

## Supported Tags

### Standard Tags

#### @param

Documents method/function parameters:

```php
/**
 * @param string $name The user's name
 * @param int $age The user's age
 * @param array<string> $roles User roles
 */
public function create(string $name, int $age, array $roles): void
```

Access:

```php
foreach ($method->docBlock->params as $param) {
    echo $param->name;        // "name", "age", "roles"
    echo $param->type;        // "string", "int", "array<string>"
    echo $param->description; // Description text
}
```

#### @return

Documents return value:

```php
/**
 * @return User|null The found user or null
 */
public function find(int $id): ?User
```

Access:

```php
$return = $method->docBlock->return;
if ($return !== null) {
    echo $return->type;        // "User|null"
    echo $return->description; // "The found user or null"
}
```

#### @var

Documents variable/property type:

```php
/**
 * @var array<string, mixed> Configuration data
 */
private array $config;
```

Access:

```php
$var = $property->docBlock->var;
if ($var !== null) {
    echo $var->type;        // "array<string, mixed>"
    echo $var->description; // "Configuration data"
}
```

#### @throws

Documents exceptions:

```php
/**
 * @throws InvalidArgumentException If name is empty
 * @throws DatabaseException If save fails
 */
public function save(): void
```

Access:

```php
foreach ($method->docBlock->throws as $throws) {
    echo $throws->type;        // Exception class name
    echo $throws->description; // When it's thrown
}
```

### Custom Tags

All other tags are captured as custom tags:

```php
/**
 * User authentication method
 *
 * @author John Doe
 * @version 1.0.0
 * @since 2024-01-01
 * @deprecated Use authenticateV2() instead
 * @see authenticateV2()
 * @link https://docs.example.com/auth
 * @todo Add 2FA support
 * @internal For internal use only
 * @api
 */
public function authenticate(): bool
```

Access:

```php
foreach ($method->docBlock->custom as $tag) {
    match ($tag->type) {
        'author' => echo "Author: {$tag->description}",
        'version' => echo "Version: {$tag->description}",
        'deprecated' => echo "Deprecated: {$tag->description}",
        'see' => echo "See also: {$tag->description}",
        'link' => echo "Link: {$tag->description}",
        'todo' => echo "TODO: {$tag->description}",
        default => echo "{$tag->type}: {$tag->description}",
    };
}
```

## Advanced Examples

### Example 1: Generate API Documentation

```php
use Aurora\Reflection\Reader;

function generateApiDocs(string $className): string
{
    $reader = new Reader();
    $metadata = $reader->read($className);

    $docs = "# {$metadata->class->shortName}\n\n";
    $docs .= $metadata->class->docBlock?->summary . "\n\n";

    foreach ($metadata->methods as $method) {
        if ($method->modifier->visibility !== Visibility::Public) {
            continue;
        }

        $docs .= "## {$method->name}()\n\n";
        $docs .= $method->docBlock?->summary . "\n\n";

        // Parameters
        if (!empty($method->docBlock?->params)) {
            $docs .= "**Parameters:**\n\n";
            foreach ($method->docBlock->params as $param) {
                $docs .= "- `{$param->name}` ({$param->type}): {$param->description}\n";
            }
            $docs .= "\n";
        }

        // Return
        if ($method->docBlock?->return !== null) {
            $return = $method->docBlock->return;
            $docs .= "**Returns:** `{$return->type}` - {$return->description}\n\n";
        }

        // Exceptions
        if (!empty($method->docBlock?->throws)) {
            $docs .= "**Throws:**\n\n";
            foreach ($method->docBlock->throws as $throws) {
                $docs .= "- `{$throws->type}`: {$throws->description}\n";
            }
            $docs .= "\n";
        }
    }

    return $docs;
}

echo generateApiDocs(User::class);
```

### Example 2: Validate DocBlock Completeness

```php
function validateDocBlocks(string $className): array
{
    $reader = new Reader();
    $metadata = $reader->read($className);
    $issues = [];

    foreach ($metadata->methods as $method) {
        if ($method->modifier->visibility !== Visibility::Public) {
            continue;
        }

        // Check if method has DocBlock
        if ($method->docBlock === null) {
            $issues[] = "Method {$method->name} is missing DocBlock";
            continue;
        }

        // Check if summary exists
        if (empty($method->docBlock->summary)) {
            $issues[] = "Method {$method->name} is missing summary";
        }

        // Check if all parameters are documented
        foreach ($method->parameters as $param) {
            $documented = false;
            foreach ($method->docBlock->params as $docParam) {
                if ($docParam->name === $param->name) {
                    $documented = true;
                    break;
                }
            }

            if (!$documented) {
                $issues[] = "Parameter \${$param->name} in {$method->name} is not documented";
            }
        }

        // Check if return type is documented
        if ($method->returnType !== null && $method->docBlock->return === null) {
            $issues[] = "Method {$method->name} return value is not documented";
        }
    }

    return $issues;
}

$issues = validateDocBlocks(User::class);
foreach ($issues as $issue) {
    echo "⚠️  {$issue}\n";
}
```

### Example 3: Extract Deprecations

```php
function findDeprecatedMembers(string $className): array
{
    $reader = new Reader();
    $metadata = $reader->read($className);
    $deprecated = [];

    // Check methods
    foreach ($metadata->methods as $method) {
        if ($method->docBlock === null) {
            continue;
        }

        foreach ($method->docBlock->custom as $tag) {
            if ($tag->type === 'deprecated') {
                $deprecated[] = [
                    'type' => 'method',
                    'name' => $method->name,
                    'reason' => $tag->description,
                ];
            }
        }
    }

    // Check properties
    foreach ($metadata->properties as $property) {
        if ($property->docBlock === null) {
            continue;
        }

        foreach ($property->docBlock->custom as $tag) {
            if ($tag->type === 'deprecated') {
                $deprecated[] = [
                    'type' => 'property',
                    'name' => $property->name,
                    'reason' => $tag->description,
                ];
            }
        }
    }

    return $deprecated;
}

$deprecated = findDeprecatedMembers(User::class);
foreach ($deprecated as $item) {
    echo "⚠️  Deprecated {$item['type']}: {$item['name']} - {$item['reason']}\n";
}
```

## Handling Missing DocBlocks

Not all members may have DocBlocks. Always check for null:

```php
// Safe access
$summary = $method->docBlock?->summary ?? 'No description';

// Conditional check
if ($property->docBlock !== null) {
    echo $property->docBlock->summary;
} else {
    echo "No documentation available";
}
```

## Related Documentation

- [Core Concepts](core-concepts.md) - Metadata structure
- [Type System](type-system.md) - Understanding types
- [Best Practices](best-practices.md) - Usage tips
