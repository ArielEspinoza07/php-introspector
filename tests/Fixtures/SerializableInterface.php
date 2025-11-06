<?php

declare(strict_types=1);

namespace Aurora\Reflection\Tests\Fixtures;

/**
 * Interface for serializable objects
 *
 * Defines methods for converting objects to and from arrays.
 */
interface SerializableInterface
{
    /**
     * Serialize format version
     */
    public const VERSION = '1.0.0';

    /**
     * Default serialization format
     */
    public const FORMAT_JSON = 'json';

    /**
     * Alternative serialization format
     */
    public const FORMAT_ARRAY = 'array';

    /**
     * Create an instance from array data
     *
     * @param array<string, mixed> $data The data to hydrate from
     * @return static A new instance
     */
    public static function fromArray(array $data): static;

    /**
     * Convert the object to an array
     *
     * @return array<string, mixed> The object data as array
     */
    public function toArray(): array;

    /**
     * Convert the object to JSON string
     *
     * @param int $flags JSON encoding flags
     * @return string JSON representation
     */
    public function toJson(int $flags = 0): string;
}
