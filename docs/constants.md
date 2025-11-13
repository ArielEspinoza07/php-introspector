# Constants

Full support for class constants with visibility, types, and metadata.

## Basic Usage

```php
class Config
{
    public const API_VERSION = '2.0';
    protected const int MAX_RETRIES = 3;
    private const string SECRET = 'key';
    final public const DEBUG = false;
}
```

Access:

```php
use Introspector\Reader;

$reader = new Reader();
$metadata = $reader->read(Config::class);

foreach ($metadata->constants as $constant) {
    echo $constant->name;              // "API_VERSION"
    var_dump($constant->value);        // "2.0"
    echo $constant->visibility->value; // "public", "protected", "private"
    echo $constant->isFinal;           // true/false (PHP 8.1+)
    echo $constant->type?->name;       // "string", "int", etc. (PHP 8.3+)
}
```

## Constant Visibility

```php
class Database
{
    public const HOST = 'localhost';       // Accessible everywhere
    protected const PORT = 3306;           // Accessible in class and children
    private const PASSWORD = 'secret';     // Only in this class
}

$metadata = $reader->read(Database::class);

foreach ($metadata->constants as $constant) {
    $visibility = $constant->visibility;

    match ($visibility) {
        Visibility::Public => echo "Public constant",
        Visibility::Protected => echo "Protected constant",
        Visibility::Private => echo "Private constant",
    };
}
```

## Typed Constants (PHP 8.3+)

```php
class Settings
{
    public const string NAME = 'App';
    public const int VERSION = 1;
    public const array CONFIG = ['key' => 'value'];
}

foreach ($metadata->constants as $constant) {
    if ($constant->type !== null) {
        echo "{$constant->name} is of type {$constant->type->name}";
    }
}
```

## Final Constants (PHP 8.1+)

```php
class BaseConfig
{
    final public const VERSION = '1.0';
}

// This would cause an error:
// class ExtendedConfig extends BaseConfig
// {
//     public const VERSION = '2.0'; // Error!
// }

$metadata = $reader->read(BaseConfig::class);

foreach ($metadata->constants as $constant) {
    if ($constant->isFinal) {
        echo "{$constant->name} cannot be overridden";
    }
}
```

## Member Source Tracking

Constants can come from interfaces, traits, or parent classes:

```php
interface Configurable
{
    public const DEFAULT_TIMEOUT = 30;
}

class Service implements Configurable
{
    public const MAX_RETRIES = 3;
}

$metadata = $reader->read(Service::class);

foreach ($metadata->constants as $constant) {
    $source = $constant->declaringSource;

    if ($source->type === SourceType::Interface_) {
        echo "{$constant->name} from interface {$source->shortName}";
    }

    if ($source->type === SourceType::Self_) {
        echo "{$constant->name} declared in Service";
    }
}
```

## DocBlocks

Constants can have DocBlocks:

```php
class Status
{
    /**
     * User is active and can log in
     *
     * @var string
     */
    public const ACTIVE = 'active';

    /**
     * User is temporarily suspended
     *
     * @deprecated Use INACTIVE instead
     */
    public const SUSPENDED = 'suspended';
}

foreach ($metadata->constants as $constant) {
    if ($constant->docBlock !== null) {
        echo $constant->docBlock->summary;
    }
}
```

## Attributes

Constants can have attributes (PHP 8+):

```php
class Status
{
    #[Label('Active User')]
    #[Color('green')]
    public const ACTIVE = 'active';

    #[Label('Inactive User')]
    #[Color('gray')]
    public const INACTIVE = 'inactive';
}

foreach ($metadata->constants as $constant) {
    foreach ($constant->attributes as $attr) {
        if ($attr->name === 'Label') {
            echo "{$constant->name}: {$attr->arguments[0]}";
        }
    }
}
```

## Practical Examples

### Example 1: Enum Metadata

```php
function getEnumValues(string $className): array
{
    $reader = new Reader();
    $metadata = $reader->read($className);
    $values = [];

    foreach ($metadata->constants as $constant) {
        $values[$constant->name] = [
            'value' => $constant->value,
            'label' => null,
        ];

        foreach ($constant->attributes as $attr) {
            if ($attr->name === 'Label') {
                $values[$constant->name]['label'] = $attr->arguments[0];
            }
        }
    }

    return $values;
}

$values = getEnumValues(Status::class);
// ['ACTIVE' => ['value' => 'active', 'label' => 'Active User'], ...]
```

## Related Documentation

- [Core Concepts](core-concepts.md) - Metadata structure
- [Member Source Tracking](member-source-tracking.md) - Track constant origin
- [Attributes](attributes.md) - Constant attributes
