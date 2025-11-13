# PHP Introspector

A modern, type-safe PHP reflection library that extracts comprehensive metadata from classes, including properties, methods, constants, DocBlocks, and more.

[![PHP Version](https://img.shields.io/badge/php-%5E8.3-blue)](https://www.php.net/)
[![License](https://img.shields.io/badge/license-MIT-green)](LICENSE)
[![CI](https://github.com/ArielEspinoza07/php-introspector/actions/workflows/ci.yml/badge.svg)](https://github.com/ArielEspinoza07/php-introspector/actions/workflows/ci.yml)

## Features

- 🔍 **Complete Class Metadata** - Extract all information about classes, interfaces, traits, and enums
- 📍 **Member Source Tracking** - Know exactly where each member comes from (trait, parent, interface, or self)
- 📝 **Rich DocBlock Parsing** - Full support for `@param`, `@return`, `@var`, `@throws`, and custom tags
- 🎯 **Type Resolution** - Handles union types, intersection types, and special types (`self`, `parent`, `static`)
- 🏷️ **Attributes Support** - PHP 8+ attributes with arguments
- 🔒 **Visibility & Modifiers** - Track public/protected/private, static, readonly, final, abstract
- 💾 **Optional Caching** - Built-in PSR-16 compatible caching layer
- 🎨 **Value Objects** - Immutable, type-safe metadata structures
- 🔧 **PHP 8.2+ Ready** - Leverages modern PHP features

## Installation

```bash
composer require arielespinoza07/php-introspector
```

## Quick Start

```php
use Introspector\Reader;

$reader = new Reader();
$metadata = $reader->read(MyClass::class);

// Access class information
echo $metadata->class->name;
echo $metadata->class->type->value;
echo $metadata->class->modifier->isFinal;

// Access properties with source tracking
foreach ($metadata->properties as $property) {
    echo $property->name;
    echo $property->type?->name;
    echo $property->declaringSource->type->value; // "self", "trait", "parent", "interface"
}

// Access methods
foreach ($metadata->methods as $method) {
    echo $method->name;
    echo $method->returnType?->name;
    echo $method->docBlock?->summary;
}

// Access constants
foreach ($metadata->constants as $constant) {
    echo $constant->name;
    echo $constant->value;
    echo $constant->visibility->value;
}
```

## Documentation

### Getting Started
- **[Installation](docs/installation.md)** - Requirements and setup
- **[Core Concepts](docs/core-concepts.md)** - Understanding the metadata structure

### Key Features
- **[Member Source Tracking](docs/member-source-tracking.md)** ⭐ **NEW** - Track where members originate
- **[DocBlocks](docs/docblocks.md)** - Parse and access DocBlock information
- **[Type System](docs/type-system.md)** - Work with PHP types (union, intersection, nullable, generics)
- **[Attributes](docs/attributes.md)** - PHP 8+ attributes support
- **[Constants](docs/constants.md)** - Class constants with visibility and types

### Advanced Topics
- **[Traits and Inheritance](docs/traits-and-inheritance.md)** - Working with traits and class hierarchies
- **[Caching](docs/caching.md)** - Performance optimization with caching
- **[JSON Output](docs/json-output.md)** - Export metadata as JSON
- **[Best Practices](docs/best-practices.md)** - Performance tips and patterns

## Member Source Tracking

**NEW FEATURE** - Track the origin of every class member:

```php
use Introspector\Enums\SourceType;

foreach ($metadata->properties as $property) {
    match ($property->declaringSource->type) {
        SourceType::Self_ => echo "{$property->name} declared in this class",
        SourceType::Trait_ => echo "{$property->name} from {$property->declaringSource->shortName} trait",
        SourceType::Parent_ => echo "{$property->name} inherited from parent",
        SourceType::Interface_ => echo "{$property->name} from interface",
    };
}
```

[Learn more about Member Source Tracking →](docs/member-source-tracking.md)

## Performance

PHP Introspector is designed for production use with built-in caching:

```php
use Introspector\Cache\CachedReader;
use Introspector\Cache\ArrayCache;

$cache = new ArrayCache();
$cachedReader = new CachedReader(new Reader(), $cache);

// First call: ~5-15ms
$metadata = $cachedReader->read(User::class);

// Subsequent calls: ~0.05-0.1ms (100x faster!)
$metadata = $cachedReader->read(User::class);
```

[Learn more about Caching →](docs/caching.md)

## Real-World Example

```php
trait TimestampTrait
{
    private ?DateTimeImmutable $createdAt = null;
    public function touch(): void { /* ... */ }
}

interface Jsonable
{
    public function toJson(): string;
}

class User implements Jsonable
{
    use TimestampTrait;

    private string $name;
    public function toJson(): string { /* ... */ }
}

$reader = new Reader();
$metadata = $reader->read(User::class);

foreach ($metadata->properties as $property) {
    echo "{$property->name}: {$property->declaringSource->type->value}\n";
}
// Output:
// createdAt: trait (from TimestampTrait)
// name: self

foreach ($metadata->methods as $method) {
    echo "{$method->name}: {$method->declaringSource->type->value}\n";
}
// Output:
// touch: trait (from TimestampTrait)
// toJson: interface (from Jsonable)
```

## Requirements

- PHP 8.2 or higher

## Testing

The library includes comprehensive test fixtures demonstrating all features:

```bash
composer test
```

## Contributing

Contributions are welcome! Please ensure:

1. PHP 8.2+ compatibility
2. Type safety (strict types)
3. PHPDoc for all public APIs
4. Tests for new features

## License

MIT License. See [LICENSE](LICENSE) for details.

## Credits

Built with ❤️ by [Ariel Espinoza](https://github.com/ArielEspinoza07).

---

**Need help?** [Open an issue](https://github.com/ArielEspinoza07/php-introspector/issues) | [Read the docs](docs/)
