# Core Concepts

## Metadata Structure

The `Metadata` object is the main entry point and contains all information about a class:

```php
Metadata {
    +class: ClassMetadata          // Class-level information
    +constructor: ?ConstructorMetadata
    +properties: array<PropertyMetadata>
    +methods: array<MethodMetadata>
    +constants: array<ConstantMetadata>
}
```

## Class Types

The library distinguishes between different class types using the `ClassType` enum:

```php
use Introspector\Enums\ClassType;

match ($metadata->class->type) {
    ClassType::Class_ => 'Regular class',
    ClassType::Interface_ => 'Interface',
    ClassType::Trait_ => 'Trait',
    ClassType::Enum_ => 'Enum (PHP 8.1+)',
    ClassType::Anonymous_ => 'Anonymous class',
};
```

### Example

```php
use Introspector\Reader;
use Introspector\Enums\ClassType;

$reader = new Reader();

// Check class type
$metadata = $reader->read(MyClass::class);

if ($metadata->class->type === ClassType::Class_) {
    echo "This is a regular class";
}

if ($metadata->class->type === ClassType::Trait_) {
    echo "This is a trait";
}
```

## Visibility

All class members (properties, methods, constants) use the `Visibility` enum:

```php
use Introspector\Enums\Visibility;

// Check property visibility
foreach ($metadata->properties as $property) {
    match ($property->modifier->visibility) {
        Visibility::Public => echo "Public property",
        Visibility::Protected => echo "Protected property",
        Visibility::Private => echo "Private property",
    };
}

// Check method visibility
foreach ($metadata->methods as $method) {
    if ($method->modifier->visibility === Visibility::Public) {
        echo "{$method->name} is public";
    }
}

// Check constant visibility (PHP 7.1+)
foreach ($metadata->constants as $constant) {
    echo $constant->visibility->value; // "public", "protected", "private"
}
```

## Class Metadata

The `ClassMetadata` object contains class-level information:

```php
$class = $metadata->class;

echo $class->name;       // "App\Models\User"
echo $class->shortName;  // "User"
echo $class->namespace;  // "App\Models"
echo $class->file;       // "/path/to/User.php"
echo $class->type;       // ClassType enum

// Modifiers
echo $class->modifier->isFinal;     // bool
echo $class->modifier->isAbstract;  // bool
echo $class->modifier->isReadonly;  // bool (PHP 8.2+)

// Inheritance and composition
var_dump($class->extends);     // string|null - parent class name
var_dump($class->implements);  // array - interfaces
var_dump($class->traits);      // array - traits

// DocBlock
echo $class->docBlock?->summary;
echo $class->docBlock?->description;

// Attributes
foreach ($class->attributes as $attr) {
    echo $attr->name;
}
```

## Property Metadata

Each property contains detailed information:

```php
foreach ($metadata->properties as $property) {
    echo $property->name;
    echo $property->hasDefaultValue;
    var_dump($property->defaultValue);

    // Type information
    echo $property->type?->name;
    echo $property->type?->isBuiltin;
    echo $property->type?->isNullable;

    // Modifiers
    echo $property->modifier->visibility;
    echo $property->modifier->isStatic;
    echo $property->modifier->isReadonly;
    echo $property->modifier->isPromoted;

    // Source tracking
    echo $property->declaringSource->type;
    echo $property->declaringSource->className;

    // DocBlock
    echo $property->docBlock?->summary;
    echo $property->docBlock?->var?->type;
}
```

## Method Metadata

Each method contains detailed information:

```php
foreach ($metadata->methods as $method) {
    echo $method->name;

    // Modifiers
    echo $method->modifier->visibility;
    echo $method->modifier->isStatic;
    echo $method->modifier->isFinal;
    echo $method->modifier->isAbstract;

    // Return type
    echo $method->returnType?->name;
    echo $method->returnType?->isNullable;

    // Parameters
    foreach ($method->parameters as $param) {
        echo $param->name;
        echo $param->type?->name;
        echo $param->hasDefaultValue;
        var_dump($param->defaultValue);
    }

    // Source tracking
    echo $method->declaringSource->type;
    echo $method->declaringSource->className;

    // Line numbers
    echo $method->lines?->start;
    echo $method->lines?->end;

    // DocBlock
    echo $method->docBlock?->summary;
    foreach ($method->docBlock?->params ?? [] as $param) {
        echo $param->name;
        echo $param->type;
        echo $param->description;
    }
}
```

## Constant Metadata

Each constant contains detailed information:

```php
foreach ($metadata->constants as $constant) {
    echo $constant->name;
    var_dump($constant->value);
    echo $constant->visibility;
    echo $constant->isFinal; // PHP 8.1+

    // Type (PHP 8.3+)
    echo $constant->type?->name;

    // Source tracking
    echo $constant->declaringSource->type;
    echo $constant->declaringSource->className;

    // DocBlock
    echo $constant->docBlock?->summary;
}
```

## Constructor Metadata

Constructor information is available separately:

```php
$constructor = $metadata->constructor;

if ($constructor !== null) {
    echo $constructor->modifier->visibility;

    foreach ($constructor->parameters as $param) {
        echo $param->name;
        echo $param->type?->name;
        echo $param->isPromoted; // Constructor property promotion
    }

    echo $constructor->docBlock?->summary;
}
```

## JSON Serialization

All metadata objects implement `JsonSerializable`:

```php
use Introspector\Reader;

$reader = new Reader();
$metadata = $reader->read(User::class);

// Convert to array
$array = $metadata->toArray();

// Convert to JSON
$json = json_encode($metadata, JSON_PRETTY_PRINT);
file_put_contents('metadata.json', $json);
```

## Related Documentation

- [Member Source Tracking](member-source-tracking.md) - Track where members originate
- [Type System](type-system.md) - Understanding PHP types
- [DocBlocks](docblocks.md) - DocBlock parsing
- [JSON Output](json-output.md) - Complete JSON structure
