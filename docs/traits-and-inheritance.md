# Traits and Inheritance

PHP Introspector automatically handles traits, inheritance, and interface implementation.

## Traits

Traits are automatically detected and their members are included in the class metadata.

### Basic Trait Usage

```php
trait TimestampTrait
{
    private ?DateTimeImmutable $createdAt = null;
    private ?DateTimeImmutable $updatedAt = null;

    protected function touch(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }
}

class User
{
    use TimestampTrait;

    private string $name;
}
```

Access trait information:

```php
use Introspector\Reader;

$reader = new Reader();
$metadata = $reader->read(User::class);

// List traits
foreach ($metadata->class->traits as $trait) {
    echo $trait->className;  // "TimestampTrait"
    echo $trait->shortName;  // "TimestampTrait"
    echo $trait->namespace;  // "App\Traits"
}

// Properties include trait properties
foreach ($metadata->properties as $property) {
    echo $property->name; // "createdAt", "updatedAt", "name"
}

// Methods include trait methods
foreach ($metadata->methods as $method) {
    echo $method->name; // "touch"
}
```

### Identifying Trait Members

Use `declaringSource` to know which members come from traits:

```php
use Introspector\Enums\SourceType;

foreach ($metadata->properties as $property) {
    if ($property->declaringSource->type === SourceType::Trait_) {
        echo "{$property->name} from {$property->declaringSource->shortName}";
        // "createdAt from TimestampTrait"
        // "updatedAt from TimestampTrait"
    }
}

foreach ($metadata->methods as $method) {
    if ($method->declaringSource->type === SourceType::Trait_) {
        echo "{$method->name} from {$method->declaringSource->shortName}";
        // "touch from TimestampTrait"
    }
}
```

### Nested Traits

Traits can use other traits:

```php
trait LoggerTrait
{
    protected function log(string $message): void { /* ... */ }
}

trait TimestampTrait
{
    use LoggerTrait;

    private ?DateTimeImmutable $createdAt = null;

    protected function touch(): void
    {
        $this->updatedAt = new DateTimeImmutable();
        $this->log('Timestamp updated');
    }
}

class User
{
    use TimestampTrait;
}
```

All trait members are detected, including nested ones:

```php
$metadata = $reader->read(User::class);

foreach ($metadata->methods as $method) {
    echo "{$method->name} from {$method->declaringSource->shortName}\n";
}
// touch from TimestampTrait
// log from LoggerTrait
```

## Inheritance

Class inheritance is fully supported.

### Basic Inheritance

```php
abstract class Model
{
    protected int $id;

    abstract public function save(): void;

    public function getId(): int
    {
        return $this->id;
    }
}

class User extends Model
{
    private string $name;

    public function save(): void
    {
        // Implementation
    }
}
```

Access inheritance information:

```php
$metadata = $reader->read(User::class);

// Parent class
if ($metadata->class->extends !== null) {
    echo "Extends: {$metadata->class->extends->className}"; // "Model"
}

// All properties (including inherited)
foreach ($metadata->properties as $property) {
    echo $property->name; // "id", "name"

    if ($property->declaringSource->type === SourceType::Parent_) {
        echo "  (inherited from {$property->declaringSource->shortName})";
    }
}

// All methods (including inherited)
foreach ($metadata->methods as $method) {
    echo $method->name; // "save", "getId"

    if ($method->declaringSource->type === SourceType::Parent_) {
        echo "  (inherited from {$method->declaringSource->shortName})";
    }
}
```

### Abstract Classes

```php
$metadata = $reader->read(Model::class);

// Check if abstract
echo $metadata->class->modifier->isAbstract; // true

// Find abstract methods
foreach ($metadata->methods as $method) {
    if ($method->modifier->isAbstract) {
        echo "{$method->name} is abstract";
    }
}
```

### Final Classes and Methods

```php
final class User
{
    final public function getId(): int
    {
        return $this->id;
    }
}

$metadata = $reader->read(User::class);

// Check if class is final
echo $metadata->class->modifier->isFinal; // true

// Check if method is final
foreach ($metadata->methods as $method) {
    if ($method->modifier->isFinal) {
        echo "{$method->name} cannot be overridden";
    }
}
```

## Interfaces

Interfaces are automatically detected.

### Basic Interface Implementation

```php
interface Jsonable
{
    public function toJson(): string;
}

interface Arrayable
{
    public function toArray(): array;
}

class User implements Jsonable, Arrayable
{
    public function toJson(): string { /* ... */ }
    public function toArray(): array { /* ... */ }
}
```

Access interface information:

```php
$metadata = $reader->read(User::class);

// List interfaces
foreach ($metadata->class->implements as $interface) {
    echo $interface->className;  // "Jsonable", "Arrayable"
    echo $interface->shortName;  // "Jsonable", "Arrayable"
    echo $interface->namespace;  // ""
}

// Identify interface methods
foreach ($metadata->methods as $method) {
    if ($method->declaringSource->type === SourceType::Interface_) {
        echo "{$method->name} from {$method->declaringSource->shortName}";
    }
}
```

### Interface Constants

Interfaces can define constants:

```php
interface HttpStatus
{
    public const OK = 200;
    public const NOT_FOUND = 404;
}

class Response implements HttpStatus
{
    // Constants are inherited
}

$metadata = $reader->read(Response::class);

foreach ($metadata->constants as $constant) {
    if ($constant->declaringSource->type === SourceType::Interface_) {
        echo "{$constant->name} from {$constant->declaringSource->shortName}";
    }
}
```

## Practical Examples

### Example 1: Class Hierarchy

```php
function getClassHierarchy(string $className): array
{
    $reader = new Reader();
    $metadata = $reader->read($className);
    $hierarchy = [$className];

    $current = $metadata->class->extends;
    while ($current !== null) {
        $hierarchy[] = $current->className;
        $parentMeta = $reader->read($current->className);
        $current = $parentMeta->class->extends;
    }

    return $hierarchy;
}

$hierarchy = getClassHierarchy(User::class);
// ["User", "Model", "BaseEntity"]
```

### Example 2: Find All Traits

```php
function getAllTraits(string $className): array
{
    $reader = new Reader();
    $metadata = $reader->read($className);
    $traits = [];

    foreach ($metadata->class->traits as $trait) {
        $traits[] = $trait->className;

        // Get traits used by this trait (nested)
        $traitMeta = $reader->read($trait->className);
        $traits = array_merge($traits, getAllTraits($trait->className));
    }

    return array_unique($traits);
}

$traits = getAllTraits(User::class);
// ["TimestampTrait", "LoggerTrait", "ValidationTrait"]
```

### Example 3: Interface Compliance Check

```php
function implementsInterface(string $className, string $interface): bool
{
    $reader = new Reader();
    $metadata = $reader->read($className);

    foreach ($metadata->class->implements as $impl) {
        if ($impl->className === $interface) {
            return true;
        }
    }

    return false;
}

if (implementsInterface(User::class, Jsonable::class)) {
    echo "User implements Jsonable";
}
```

### Example 4: Composition Analysis

```php
function analyzeComposition(string $className): array
{
    $reader = new Reader();
    $metadata = $reader->read($className);

    $analysis = [
        'traits' => count($metadata->class->traits),
        'interfaces' => count($metadata->class->implements),
        'extends' => $metadata->class->extends !== null,
        'members_from_traits' => 0,
        'members_from_parent' => 0,
        'members_from_interfaces' => 0,
        'own_members' => 0,
    ];

    foreach ($metadata->properties as $property) {
        match ($property->declaringSource->type) {
            SourceType::Trait_ => $analysis['members_from_traits']++,
            SourceType::Parent_ => $analysis['members_from_parent']++,
            SourceType::Self_ => $analysis['own_members']++,
            default => null,
        };
    }

    foreach ($metadata->methods as $method) {
        match ($method->declaringSource->type) {
            SourceType::Trait_ => $analysis['members_from_traits']++,
            SourceType::Parent_ => $analysis['members_from_parent']++,
            SourceType::Interface_ => $analysis['members_from_interfaces']++,
            SourceType::Self_ => $analysis['own_members']++,
        };
    }

    return $analysis;
}

$analysis = analyzeComposition(User::class);
print_r($analysis);
```

## Related Documentation

- [Member Source Tracking](member-source-tracking.md) - Detailed source tracking
- [Core Concepts](core-concepts.md) - Metadata structure
- [Best Practices](best-practices.md) - Usage patterns
