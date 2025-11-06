# Installation

## Requirements

- PHP 8.2 or higher

## Install via Composer

```bash
composer require aurora-php/reflection
```

## Quick Start

```php
<?php

require_once 'vendor/autoload.php';

use Aurora\Reflection\Reader;

$reader = new Reader();
$metadata = $reader->read(MyClass::class);

// Access class information
echo $metadata->class->name;
echo $metadata->class->type->value;

// Access properties
foreach ($metadata->properties as $property) {
    echo $property->name;
}

// Access methods
foreach ($metadata->methods as $method) {
    echo $method->name;
}

// Access constants
foreach ($metadata->constants as $constant) {
    echo $constant->name;
}
```

## What's Next?

- [Core Concepts](core-concepts.md) - Understand the metadata structure
- [Member Source Tracking](member-source-tracking.md) - Track where members come from
- [DocBlocks](docblocks.md) - Parse and access DocBlock information
- [Type System](type-system.md) - Work with PHP types
