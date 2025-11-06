# Attributes

PHP 8+ attributes are fully supported for classes, properties, methods, parameters, and constants.

## Overview

Attributes provide a way to add structured metadata to declarations. Aurora Reflection extracts all attributes with their arguments.

## Basic Usage

```php
#[Route('/api/users')]
class UserController
{
    #[Inject]
    private LoggerInterface $logger;

    #[Route('/api/users/{id}', methods: ['GET'])]
    #[Cache(ttl: 3600)]
    public function show(#[PathParam] int $id): Response
    {
        // ...
    }
}
```

Access attributes:

```php
use Aurora\Reflection\Reader;

$reader = new Reader();
$metadata = $reader->read(UserController::class);

// Class attributes
foreach ($metadata->class->attributes as $attr) {
    echo $attr->name;      // "Route"
    var_dump($attr->arguments); // ['/api/users']
}

// Method attributes
foreach ($metadata->methods as $method) {
    foreach ($method->attributes as $attr) {
        echo "{$method->name} has attribute: {$attr->name}";
        var_dump($attr->arguments);
    }
}

// Property attributes
foreach ($metadata->properties as $property) {
    foreach ($property->attributes as $attr) {
        echo "{$property->name} has attribute: {$attr->name}";
    }
}
```

## Attribute Metadata

Each attribute has:

```php
AttributeMetadata {
    +name: string              // Attribute class name
    +arguments: array          // Attribute arguments (named and positional)
}
```

## Class Attributes

```php
#[Table('users')]
#[Entity]
#[Cache(ttl: 3600)]
class User
{
    // ...
}
```

Access:

```php
$metadata = $reader->read(User::class);

foreach ($metadata->class->attributes as $attr) {
    match ($attr->name) {
        'Table' => echo "Table name: {$attr->arguments[0]}",
        'Entity' => echo "This is an entity",
        'Cache' => echo "Cache TTL: {$attr->arguments['ttl']}",
        default => echo "Unknown attribute: {$attr->name}",
    };
}
```

## Property Attributes

```php
class User
{
    #[Column('user_name', type: 'string', length: 255)]
    #[Index]
    private string $name;

    #[Column('user_email', unique: true)]
    #[Validate('email')]
    private string $email;
}
```

Access:

```php
foreach ($metadata->properties as $property) {
    echo "Property: {$property->name}\n";

    foreach ($property->attributes as $attr) {
        if ($attr->name === 'Column') {
            echo "  Column: {$attr->arguments[0]}\n";
            echo "  Type: {$attr->arguments['type']}\n";
        }

        if ($attr->name === 'Validate') {
            echo "  Validation: {$attr->arguments[0]}\n";
        }
    }
}
```

## Method Attributes

```php
class UserController
{
    #[Route('/users', methods: ['GET', 'POST'])]
    #[Auth(roles: ['admin', 'user'])]
    #[RateLimit(requests: 100, period: 60)]
    public function index(): Response
    {
        // ...
    }

    #[Deprecated('Use indexV2() instead')]
    #[Route('/users/old')]
    public function oldIndex(): Response
    {
        // ...
    }
}
```

Access:

```php
foreach ($metadata->methods as $method) {
    echo "Method: {$method->name}\n";

    foreach ($method->attributes as $attr) {
        match ($attr->name) {
            'Route' => echo "  Path: {$attr->arguments[0]}, Methods: " .
                       implode(', ', $attr->arguments['methods']) . "\n",
            'Auth' => echo "  Required roles: " .
                     implode(', ', $attr->arguments['roles']) . "\n",
            'RateLimit' => echo "  Limit: {$attr->arguments['requests']} per {$attr->arguments['period']}s\n",
            'Deprecated' => echo "  ⚠️  {$attr->arguments[0]}\n",
            default => null,
        };
    }
}
```

## Parameter Attributes

```php
class UserController
{
    public function update(
        #[PathParam] int $id,
        #[Body] UserDto $data,
        #[Header('X-API-Key')] string $apiKey
    ): Response {
        // ...
    }
}
```

Access:

```php
foreach ($metadata->methods as $method) {
    foreach ($method->parameters as $param) {
        echo "Parameter: {$param->name}\n";

        foreach ($param->attributes as $attr) {
            echo "  Attribute: {$attr->name}";

            if (!empty($attr->arguments)) {
                echo " (" . implode(', ', $attr->arguments) . ")";
            }

            echo "\n";
        }
    }
}
```

## Constant Attributes

```php
class Status
{
    #[Label('Draft')]
    #[Color('gray')]
    public const DRAFT = 'draft';

    #[Label('Published')]
    #[Color('green')]
    public const PUBLISHED = 'published';
}
```

Access:

```php
foreach ($metadata->constants as $constant) {
    echo "Constant: {$constant->name} = {$constant->value}\n";

    foreach ($constant->attributes as $attr) {
        if ($attr->name === 'Label') {
            echo "  Label: {$attr->arguments[0]}\n";
        }

        if ($attr->name === 'Color') {
            echo "  Color: {$attr->arguments[0]}\n";
        }
    }
}
```

## Practical Examples

### Example 1: Route Discovery

```php
function discoverRoutes(array $controllers): array
{
    $reader = new Reader();
    $routes = [];

    foreach ($controllers as $controllerClass) {
        $metadata = $reader->read($controllerClass);

        foreach ($metadata->methods as $method) {
            foreach ($method->attributes as $attr) {
                if ($attr->name === 'Route') {
                    $routes[] = [
                        'path' => $attr->arguments[0],
                        'methods' => $attr->arguments['methods'] ?? ['GET'],
                        'controller' => $controllerClass,
                        'action' => $method->name,
                    ];
                }
            }
        }
    }

    return $routes;
}

$routes = discoverRoutes([
    UserController::class,
    PostController::class,
]);

foreach ($routes as $route) {
    echo "{$route['path']} => {$route['controller']}@{$route['action']}\n";
}
```

### Example 2: Validation Rules

```php
function extractValidationRules(string $className): array
{
    $reader = new Reader();
    $metadata = $reader->read($className);
    $rules = [];

    foreach ($metadata->properties as $property) {
        foreach ($property->attributes as $attr) {
            if ($attr->name === 'Validate') {
                $rules[$property->name] = $attr->arguments;
            }
        }
    }

    return $rules;
}

$rules = extractValidationRules(UserDto::class);
// ['email' => ['email'], 'age' => ['integer', 'min:18'], ...]
```

### Example 3: Dependency Injection

```php
function resolveInjections(string $className): array
{
    $reader = new Reader();
    $metadata = $reader->read($className);
    $injections = [];

    foreach ($metadata->properties as $property) {
        foreach ($property->attributes as $attr) {
            if ($attr->name === 'Inject') {
                $injections[$property->name] = [
                    'type' => $property->type?->name,
                    'name' => $attr->arguments['name'] ?? null,
                ];
            }
        }
    }

    return $injections;
}

$injections = resolveInjections(UserService::class);
// ['logger' => ['type' => 'LoggerInterface', 'name' => null], ...]
```

### Example 4: API Documentation

```php
function generateApiDocs(string $controllerClass): array
{
    $reader = new Reader();
    $metadata = $reader->read($controllerClass);
    $endpoints = [];

    foreach ($metadata->methods as $method) {
        $route = null;
        $auth = null;

        foreach ($method->attributes as $attr) {
            if ($attr->name === 'Route') {
                $route = [
                    'path' => $attr->arguments[0],
                    'methods' => $attr->arguments['methods'] ?? ['GET'],
                ];
            }

            if ($attr->name === 'Auth') {
                $auth = $attr->arguments['roles'] ?? [];
            }
        }

        if ($route !== null) {
            $endpoints[] = [
                'route' => $route,
                'method' => $method->name,
                'auth' => $auth,
                'description' => $method->docBlock?->summary,
            ];
        }
    }

    return $endpoints;
}
```

## Built-in PHP Attributes

Aurora Reflection also captures built-in PHP attributes:

### #[Override] (PHP 8.3+)

```php
class Admin extends User
{
    #[Override]
    public function getRole(): string
    {
        return 'admin';
    }
}
```

### #[Deprecated] (PHP 8.4+)

```php
class OldClass
{
    #[Deprecated('Use NewClass instead', since: '2.0')]
    public function oldMethod(): void
    {
        // ...
    }
}
```

## Checking for Specific Attributes

```php
function hasAttribute(array $attributes, string $name): bool
{
    foreach ($attributes as $attr) {
        if ($attr->name === $name) {
            return true;
        }
    }

    return false;
}

$method = $metadata->methods['index'];

if (hasAttribute($method->attributes, 'Route')) {
    echo "This method is a route handler";
}

if (hasAttribute($method->attributes, 'Deprecated')) {
    echo "⚠️  This method is deprecated";
}
```

## Related Documentation

- [Core Concepts](core-concepts.md) - Metadata structure
- [DocBlocks](docblocks.md) - Alternative metadata approach
- [Best Practices](best-practices.md) - Usage patterns
