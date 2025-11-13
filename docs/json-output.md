# JSON Output

All metadata objects are JSON serializable for easy export and analysis.

## Basic Usage

```php
use Introspector\Reader;

$reader = new Reader();
$metadata = $reader->read(User::class);

// Convert to JSON
$json = json_encode($metadata, JSON_PRETTY_PRINT);
file_put_contents('user-metadata.json', $json);

// Convert to array
$array = $metadata->toArray();
```

## Complete Structure

Here's the complete JSON structure with all fields:

```json
{
  "class": {
    "name": "App\\Models\\User",
    "short_name": "User",
    "namespace": "App\\Models",
    "file": "/path/to/User.php",
    "type": "class",
    "lines": {
      "start": 10,
      "end": 100
    },
    "modifier": {
      "is_abstract": false,
      "is_final": true,
      "is_readonly": false,
      "is_internal": false,
      "is_anonymous": false,
      "is_instantiable": true
    },
    "doc_block": {
      "summary": "User model class",
      "description": "Represents a user in the system",
      "params": [],
      "return": null,
      "var": null,
      "throws": [],
      "custom": [
        {
          "type": "author",
          "description": "John Doe"
        }
      ]
    },
    "extends": {
      "type": "parent",
      "class_name": "App\\Models\\Model",
      "short_name": "Model",
      "namespace": "App\\Models"
    },
    "implements": [
      {
        "type": "interface",
        "class_name": "JsonSerializable",
        "short_name": "JsonSerializable",
        "namespace": ""
      }
    ],
    "traits": [
      {
        "type": "trait",
        "class_name": "App\\Traits\\TimestampTrait",
        "short_name": "TimestampTrait",
        "namespace": "App\\Traits"
      }
    ],
    "attributes": []
  },
  "constructor": {
    "modifier": {
      "visibility": "public"
    },
    "doc_block": {
      "summary": "Creates a new user",
      "description": null,
      "params": [
        {
          "name": "name",
          "type": "string",
          "description": "User's name"
        }
      ],
      "return": null,
      "var": null,
      "throws": [],
      "custom": []
    },
    "parameters": [
      {
        "name": "name",
        "is_variadic": false,
        "is_optional": false,
        "is_promoted": true,
        "position": 0,
        "is_passed_by_reference": false,
        "is_passed_by_value": true,
        "allows_null": false,
        "has_default_value": false,
        "default_value": null,
        "type": {
          "name": "string",
          "resolved_name": null,
          "is_builtin": true,
          "is_nullable": false,
          "is_union": false,
          "is_intersection": false,
          "is_special": false,
          "union_types": [],
          "intersection_types": []
        },
        "attributes": []
      }
    ],
    "attributes": []
  },
  "properties": [
    {
      "name": "name",
      "modifier": {
        "visibility": "private",
        "is_promoted": true,
        "is_default": true,
        "is_static": false,
        "is_readonly": false
      },
      "has_default_value": false,
      "default_value": null,
      "declaring_source": {
        "type": "self",
        "class_name": "App\\Models\\User",
        "short_name": "User",
        "namespace": "App\\Models"
      },
      "doc_block": null,
      "type": {
        "name": "string",
        "resolved_name": null,
        "is_builtin": true,
        "is_nullable": false,
        "is_union": false,
        "is_intersection": false,
        "is_special": false,
        "union_types": [],
        "intersection_types": []
      },
      "attributes": []
    },
    {
      "name": "createdAt",
      "modifier": {
        "visibility": "private",
        "is_promoted": false,
        "is_default": true,
        "is_static": false,
        "is_readonly": false
      },
      "has_default_value": true,
      "default_value": null,
      "declaring_source": {
        "type": "trait",
        "class_name": "App\\Traits\\TimestampTrait",
        "short_name": "TimestampTrait",
        "namespace": "App\\Traits"
      },
      "doc_block": {
        "summary": "When the entity was created",
        "description": null,
        "params": [],
        "return": null,
        "var": {
          "type": "DateTimeImmutable|null",
          "description": null
        },
        "throws": [],
        "custom": []
      },
      "type": {
        "name": "DateTimeImmutable",
        "resolved_name": null,
        "is_builtin": false,
        "is_nullable": true,
        "is_union": false,
        "is_intersection": false,
        "is_special": false,
        "union_types": [],
        "intersection_types": []
      },
      "attributes": []
    }
  ],
  "methods": [
    {
      "name": "getName",
      "modifier": {
        "is_abstract": false,
        "is_final": false,
        "is_static": false,
        "visibility": "public"
      },
      "declaring_source": {
        "type": "self",
        "class_name": "App\\Models\\User",
        "short_name": "User",
        "namespace": "App\\Models"
      },
      "lines": {
        "start": 50,
        "end": 53
      },
      "doc_block": {
        "summary": "Get user's name",
        "description": null,
        "params": [],
        "return": {
          "type": "string",
          "description": "The user's name"
        },
        "var": null,
        "throws": [],
        "custom": []
      },
      "return_type": {
        "name": "string",
        "resolved_name": null,
        "is_builtin": true,
        "is_nullable": false,
        "is_union": false,
        "is_intersection": false,
        "is_special": false,
        "union_types": [],
        "intersection_types": []
      },
      "parameters": [],
      "attributes": []
    },
    {
      "name": "touch",
      "modifier": {
        "is_abstract": false,
        "is_final": false,
        "is_static": false,
        "visibility": "protected"
      },
      "declaring_source": {
        "type": "trait",
        "class_name": "App\\Traits\\TimestampTrait",
        "short_name": "TimestampTrait",
        "namespace": "App\\Traits"
      },
      "lines": {
        "start": 42,
        "end": 45
      },
      "doc_block": {
        "summary": "Update the updated_at timestamp",
        "description": null,
        "params": [],
        "return": {
          "type": "void",
          "description": null
        },
        "var": null,
        "throws": [],
        "custom": []
      },
      "return_type": {
        "name": "void",
        "resolved_name": null,
        "is_builtin": true,
        "is_nullable": false,
        "is_union": false,
        "is_intersection": false,
        "is_special": false,
        "union_types": [],
        "intersection_types": []
      },
      "parameters": [],
      "attributes": []
    }
  ],
  "constants": [
    {
      "name": "VERSION",
      "value": "1.0.0",
      "visibility": "public",
      "declaring_source": {
        "type": "self",
        "class_name": "App\\Models\\User",
        "short_name": "User",
        "namespace": "App\\Models"
      },
      "is_final": false,
      "type": {
        "name": "string",
        "resolved_name": null,
        "is_builtin": true,
        "is_nullable": false,
        "is_union": false,
        "is_intersection": false,
        "is_special": false,
        "union_types": [],
        "intersection_types": []
      },
      "doc_block": null
    }
  ]
}
```

## Key Changes from Standard Reflection

### 1. Structured References

Instead of simple strings, references are objects:

**OLD:**
```json
"implements": ["JsonSerializable"],
"traits": ["TimestampTrait"]
```

**NEW:**
```json
"implements": [
  {
    "type": "interface",
    "class_name": "JsonSerializable",
    "short_name": "JsonSerializable",
    "namespace": ""
  }
],
"traits": [
  {
    "type": "trait",
    "class_name": "App\\Traits\\TimestampTrait",
    "short_name": "TimestampTrait",
    "namespace": "App\\Traits"
  }
]
```

### 2. Member Source Tracking

All members include `declaring_source`:

```json
"declaring_source": {
  "type": "trait",
  "class_name": "App\\Traits\\TimestampTrait",
  "short_name": "TimestampTrait",
  "namespace": "App\\Traits"
}
```

Possible types: `"self"`, `"parent"`, `"trait"`, `"interface"`

### 3. Parent Class Structure

Instead of a string, `extends` is an object or null:

```json
"extends": {
  "type": "parent",
  "class_name": "App\\Models\\Model",
  "short_name": "Model",
  "namespace": "App\\Models"
}
```

## Export Examples

### Example 1: Save to File

```php
$metadata = $reader->read(User::class);
$json = json_encode($metadata, JSON_PRETTY_PRINT);
file_put_contents('metadata/User.json', $json);
```

### Example 2: Generate for Multiple Classes

```php
$classes = [User::class, Post::class, Comment::class];

foreach ($classes as $class) {
    $metadata = $reader->read($class);
    $shortName = (new ReflectionClass($class))->getShortName();
    $json = json_encode($metadata, JSON_PRETTY_PRINT);
    file_put_contents("metadata/{$shortName}.json", $json);
}
```

### Example 3: API Response

```php
// In your API controller
public function getClassMetadata(string $className): JsonResponse
{
    $reader = new Reader();
    $metadata = $reader->read($className);

    return new JsonResponse($metadata);
}
```

## Related Documentation

- [Core Concepts](core-concepts.md) - Metadata structure
- [Member Source Tracking](member-source-tracking.md) - Understanding declaring_source
