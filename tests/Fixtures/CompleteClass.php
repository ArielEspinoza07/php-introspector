<?php

declare(strict_types=1);

namespace Aurora\Reflection\Tests\Fixtures;

use JsonSerializable;
use Override;
use SensitiveParameter;
use Stringable;

/**
 * Complete class with all possible PHP features
 *
 * This class demonstrates all features that the reflection library
 * should be able to handle, including:
 * - All visibility levels
 * - Static and readonly properties
 * - Union and intersection types
 * - Attributes
 * - DocBlocks with all tags
 * - Constants with different visibilities
 * - Traits usage
 * - Interface implementation
 * - Special types (self, static, parent)
 *
 * @author Aurora Team
 *
 * @version 1.0.0
 */
final class CompleteClass implements JsonSerializable, SerializableInterface, Stringable
{
    use TimestampTrait;

    /**
     * Public constant - API version
     */
    public const API_VERSION = '2.0';

    /**
     * Final constant (PHP 8.1+)
     */
    final public const DEBUG = false;

    /**
     * Protected constant - internal use
     */
    protected const MAX_RETRIES = 3;

    /**
     * Private constant - secret key
     */
    private const SECRET = 'private-key';

    /**
     * Public static property
     */
    public static int $instanceCount = 0;

    /**
     * Private static cache
     *
     * @var array<string, mixed>
     */
    private static array $cache = [];

    /**
     * The entity ID
     */
    private int $id;

    /**
     * The entity name
     */
    private string $name;

    /**
     * The entity status
     */
    private Status $status;

    /**
     * Optional description
     */
    private ?string $description;

    /**
     * Tags using union types
     *
     * @var array<string>|null
     */
    private ?array $tags;

    /**
     * Creates a new complete class instance
     *
     * This constructor demonstrates promoted properties, union types,
     * attributes, and default values.
     *
     * @param  int  $id  The unique identifier
     * @param  string  $name  The entity name
     * @param  Status  $status  The current status
     * @param  string|null  $description  Optional description
     * @param  array<string>|null  $tags  Optional tags
     * @param  string  $secret  Sensitive parameter (password, token, etc)
     */
    public function __construct(
        int $id,
        string $name,
        Status $status = Status::Draft,
        ?string $description = null,
        ?array $tags = null,
        #[SensitiveParameter]
        string $secret = '',
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->status = $status;
        $this->description = $description;
        $this->tags = $tags;

        self::$instanceCount++;
        $this->initializeTimestamps();
    }

    /**
     * Convert to string
     *
     * @return string String representation
     */
    #[Override]
    public function __toString(): string
    {
        return "{$this->name} (#{$this->id})";
    }

    /**
     * Create a builder for this class
     *
     * Demonstrates the `static` return type.
     *
     * @return static A new builder instance
     */
    public static function builder(): static
    {
        return new self(
            id: 0,
            name: 'default',
        );
    }

    /**
     * Find an entity by ID using union types
     *
     * @param  int  $id  The entity ID
     * @return self|null The found entity or null
     */
    public static function find(int $id): ?self
    {
        return self::$cache[$id] ?? null;
    }

    /**
     * Create from array data
     *
     * @param  array<string, mixed>  $data  The source data
     * @return static A new instance
     */
    #[Override]
    public static function fromArray(array $data): static
    {
        return new static(
            id: $data['id'],
            name: $data['name'],
            status: Status::from($data['status']),
            description: $data['description'] ?? null,
            tags: $data['tags'] ?? null,
        );
    }

    /**
     * Static factory method with variadic parameters
     *
     * @param  string  $name  The name
     * @param  string  ...$tags  Variable number of tags
     * @return self A new instance
     */
    public static function create(string $name, string ...$tags): self
    {
        return new self(
            id: self::$instanceCount + 1,
            name: $name,
            tags: $tags,
        );
    }

    /**
     * Get instance count
     *
     * @return int The total number of instances created
     */
    public static function getInstanceCount(): int
    {
        return self::$instanceCount;
    }

    /**
     * Get the entity ID
     *
     * @return int The ID
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Get the entity name
     *
     * @return string The name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Clone the instance with a new name
     *
     * Demonstrates the `self` return type.
     *
     * @param  string  $name  The new name
     * @return self A new instance
     */
    public function withName(string $name): self
    {
        return new self(
            id: $this->id,
            name: $name,
            status: $this->status,
            description: $this->description,
            tags: $this->tags,
        );
    }

    /**
     * Complex method with intersection types
     *
     * @param  SerializableInterface&JsonSerializable  $data  The data object
     * @return array<string, mixed> The processed data
     */
    public function process(SerializableInterface&JsonSerializable $data): array
    {
        return $data->toArray();
    }

    /**
     * Convert to array representation
     *
     * @return array<string, mixed> The array data
     */
    #[Override]
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'status' => $this->status->value,
            'description' => $this->description,
            'tags' => $this->tags,
        ];
    }

    /**
     * Convert to JSON string
     *
     * @param  int  $flags  JSON encoding flags
     * @return string JSON representation
     */
    #[Override]
    public function toJson(int $flags = 0): string
    {
        return json_encode($this->toArray(), $flags);
    }

    /**
     * JSON serialize
     *
     * @return array<string, mixed> The JSON data
     */
    #[Override]
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Method with reference parameter
     *
     * @param  int  &$counter  The counter to increment
     */
    public function increment(int &$counter): void
    {
        $counter++;
    }

    /**
     * Protected helper method
     *
     * @param  string  $key  The cache key
     * @return mixed The cached value
     */
    protected function getCached(string $key): mixed
    {
        return self::$cache[$key] ?? null;
    }

    /**
     * Private internal method
     */
    private function clearCache(): void
    {
        self::$cache = [];
    }
}
