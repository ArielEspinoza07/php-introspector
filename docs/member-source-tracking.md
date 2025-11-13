# Member Source Tracking

**NEW FEATURE** - Track the origin of every class member (property, method, constant).

## Overview

When a class uses traits, implements interfaces, or extends a parent class, it can be difficult to know where each member originates. PHP Introspector automatically tracks this information for you.

Every `PropertyMetadata`, `MethodMetadata`, and `ConstantMetadata` includes a `declaringSource` property that tells you exactly where that member was declared.

## Source Types

Members can originate from four different sources:

```php
use Introspector\Enums\SourceType;

SourceType::Self_       // Declared in the current class
SourceType::Parent_     // Inherited from a parent class
SourceType::Interface_  // Declared in an interface
SourceType::Trait_      // Comes from a trait
```

## DeclaringSource Object

The `declaringSource` object contains:

```php
DeclaringSource {
    +type: SourceType          // Where it comes from
    +className: string         // Full class name (e.g., "App\Traits\TimestampTrait")
    +shortName: string         // Short name (e.g., "TimestampTrait")
    +namespace: string         // Namespace (e.g., "App\Traits")
}
```

## Basic Usage

### Check Property Origin

```php
use Introspector\Reader;
use Introspector\Enums\SourceType;

$reader = new Reader();
$metadata = $reader->read(User::class);

foreach ($metadata->properties as $property) {
    $source = $property->declaringSource;

    match ($source->type) {
        SourceType::Self_ => echo "{$property->name} is declared in User class",
        SourceType::Trait_ => echo "{$property->name} comes from {$source->shortName} trait",
        SourceType::Parent_ => echo "{$property->name} is inherited from {$source->shortName}",
        SourceType::Interface_ => echo "{$property->name} is declared in {$source->shortName} interface",
    };
}
```

### Check Method Origin

```php
foreach ($metadata->methods as $method) {
    $source = $method->declaringSource;

    if ($source->type === SourceType::Trait_) {
        echo "Method {$method->name} comes from trait {$source->className}";
    }
}
```

### Check Constant Origin

```php
foreach ($metadata->constants as $constant) {
    $source = $constant->declaringSource;

    if ($source->type === SourceType::Interface_) {
        echo "Constant {$constant->name} is from interface {$source->shortName}";
    }
}
```

## Practical Examples

### Example 1: Filter Members by Source

Get only properties declared in the class itself:

```php
$ownProperties = array_filter(
    $metadata->properties,
    fn($prop) => $prop->declaringSource->type === SourceType::Self_
);

foreach ($ownProperties as $property) {
    echo $property->name;
}
```

Get only methods from traits:

```php
$traitMethods = array_filter(
    $metadata->methods,
    fn($method) => $method->declaringSource->type === SourceType::Trait_
);

foreach ($traitMethods as $method) {
    echo "{$method->name} from {$method->declaringSource->shortName}";
}
```

### Example 2: Group Members by Source

```php
$grouped = [
    'own' => [],
    'trait' => [],
    'parent' => [],
    'interface' => [],
];

foreach ($metadata->methods as $method) {
    $type = $method->declaringSource->type->value;
    $grouped[$type][] = $method;
}

echo "Own methods: " . count($grouped['self']);
echo "Trait methods: " . count($grouped['trait']);
echo "Inherited methods: " . count($grouped['parent']);
echo "Interface methods: " . count($grouped['interface']);
```

### Example 3: List Trait Members

```php
use Introspector\Enums\SourceType;

$reader = new Reader();
$metadata = $reader->read(User::class);

// Find all trait-sourced members
$traitProperties = array_filter(
    $metadata->properties,
    fn($p) => $p->declaringSource->type === SourceType::Trait_
);

$traitMethods = array_filter(
    $metadata->methods,
    fn($m) => $m->declaringSource->type === SourceType::Trait_
);

echo "Properties from traits:\n";
foreach ($traitProperties as $property) {
    echo "  - {$property->name} from {$property->declaringSource->shortName}\n";
}

echo "\nMethods from traits:\n";
foreach ($traitMethods as $method) {
    echo "  - {$method->name} from {$method->declaringSource->shortName}\n";
}
```

### Example 4: Detect Trait Usage

```php
// Check if any members come from a specific trait
function usesTimestampTrait($metadata): bool {
    foreach ($metadata->properties as $property) {
        if ($property->declaringSource->type === SourceType::Trait_ &&
            $property->declaringSource->shortName === 'TimestampTrait') {
            return true;
        }
    }

    foreach ($metadata->methods as $method) {
        if ($method->declaringSource->type === SourceType::Trait_ &&
            $method->declaringSource->shortName === 'TimestampTrait') {
            return true;
        }
    }

    return false;
}

$reader = new Reader();
$metadata = $reader->read(User::class);

if (usesTimestampTrait($metadata)) {
    echo "User class uses TimestampTrait";
}
```

### Example 5: Documentation Generator

Generate documentation showing member origins:

```php
use Introspector\Reader;
use Introspector\Enums\SourceType;

function generateMemberDocs(string $className): string {
    $reader = new Reader();
    $metadata = $reader->read($className);

    $docs = "# {$metadata->class->shortName}\n\n";

    // Document properties
    $docs .= "## Properties\n\n";
    foreach ($metadata->properties as $property) {
        $origin = match($property->declaringSource->type) {
            SourceType::Self_ => "declared here",
            SourceType::Trait_ => "from trait {$property->declaringSource->shortName}",
            SourceType::Parent_ => "inherited from {$property->declaringSource->shortName}",
            SourceType::Interface_ => "from interface {$property->declaringSource->shortName}",
        };

        $docs .= "- `{$property->name}` ({$origin})\n";
    }

    // Document methods
    $docs .= "\n## Methods\n\n";
    foreach ($metadata->methods as $method) {
        $origin = match($method->declaringSource->type) {
            SourceType::Self_ => "declared here",
            SourceType::Trait_ => "from trait {$method->declaringSource->shortName}",
            SourceType::Parent_ => "inherited from {$method->declaringSource->shortName}",
            SourceType::Interface_ => "from interface {$method->declaringSource->shortName}",
        };

        $docs .= "- `{$method->name}()` ({$origin})\n";
    }

    return $docs;
}

echo generateMemberDocs(User::class);
```

## Real-World Example

```php
<?php

use Introspector\Reader;
use Introspector\Enums\SourceType;

trait TimestampTrait
{
    private ?DateTimeImmutable $createdAt = null;
    private ?DateTimeImmutable $updatedAt = null;

    public function touch(): void {
        $this->updatedAt = new DateTimeImmutable();
    }
}

interface Jsonable
{
    public function toJson(): string;
}

abstract class Model
{
    protected int $id;

    abstract public function save(): void;
}

class User extends Model implements Jsonable
{
    use TimestampTrait;

    private string $name;
    private string $email;

    public function __construct(string $name, string $email) {
        $this->name = $name;
        $this->email = $email;
    }

    public function save(): void { /* ... */ }

    public function toJson(): string { /* ... */ }
}

// Analyze User class
$reader = new Reader();
$metadata = $reader->read(User::class);

// Properties
foreach ($metadata->properties as $property) {
    echo "{$property->name}: {$property->declaringSource->type->value}\n";
}
// Output:
// id: parent (from Model)
// createdAt: trait (from TimestampTrait)
// updatedAt: trait (from TimestampTrait)
// name: self
// email: self

// Methods
foreach ($metadata->methods as $method) {
    echo "{$method->name}: {$method->declaringSource->type->value}\n";
}
// Output:
// touch: trait (from TimestampTrait)
// __construct: self
// save: self (overrides abstract method from Model)
// toJson: interface (from Jsonable)
```

## JSON Output

The `declaringSource` is included in JSON output:

```json
{
  "properties": [
    {
      "name": "createdAt",
      "declaring_source": {
        "type": "trait",
        "class_name": "App\\Traits\\TimestampTrait",
        "short_name": "TimestampTrait",
        "namespace": "App\\Traits"
      }
    }
  ],
  "methods": [
    {
      "name": "touch",
      "declaring_source": {
        "type": "trait",
        "class_name": "App\\Traits\\TimestampTrait",
        "short_name": "TimestampTrait",
        "namespace": "App\\Traits"
      }
    }
  ]
}
```

## Benefits

✅ **Clear visibility** - Know exactly where each member comes from
✅ **Better debugging** - Quickly identify trait vs class members
✅ **Documentation** - Generate accurate API docs
✅ **Refactoring** - Safely move members between traits and classes
✅ **Code analysis** - Build tools that understand class composition

## Related Documentation

- [Core Concepts](core-concepts.md) - Metadata structure
- [Traits and Inheritance](traits-and-inheritance.md) - Working with traits
- [JSON Output](json-output.md) - Complete structure
