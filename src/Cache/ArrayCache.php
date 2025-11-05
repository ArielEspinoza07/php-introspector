<?php

declare(strict_types=1);

namespace Aurora\Reflection\Cache;

use DateInterval;

/**
 * Simple in-memory cache implementation (PSR-16 compatible)
 *
 * This cache stores items in memory for the duration of the script execution.
 * Data is lost when the script ends.
 */
final class ArrayCache implements CacheInterface
{
    /**
     * @var array<string, mixed>
     */
    private array $storage = [];

    /**
     * @var array<string, int>
     */
    private array $expirations = [];

    public function get(string $key, mixed $default = null): mixed
    {
        if (! $this->has($key)) {
            return $default;
        }

        return $this->storage[$key];
    }

    public function set(string $key, mixed $value, null|int|DateInterval $ttl = null): bool
    {
        $this->storage[$key] = $value;

        if ($ttl !== null) {
            $seconds = $ttl instanceof DateInterval
                ? (new \DateTime())->add($ttl)->getTimestamp() - time()
                : $ttl;

            $this->expirations[$key] = time() + $seconds;
        } else {
            unset($this->expirations[$key]);
        }

        return true;
    }

    public function delete(string $key): bool
    {
        unset($this->storage[$key], $this->expirations[$key]);

        return true;
    }

    public function clear(): bool
    {
        $this->storage = [];
        $this->expirations = [];

        return true;
    }

    public function has(string $key): bool
    {
        if (! array_key_exists($key, $this->storage)) {
            return false;
        }

        // Check expiration
        if (isset($this->expirations[$key]) && $this->expirations[$key] < time()) {
            $this->delete($key);

            return false;
        }

        return true;
    }

    /**
     * @param  iterable<string>  $keys
     * @return iterable<string, mixed>
     */
    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        $values = [];

        foreach ($keys as $key) {
            $values[$key] = $this->get($key, $default);
        }

        return $values;
    }

    /**
     * @param  iterable<string, mixed>  $values
     */
    public function setMultiple(iterable $values, null|int|DateInterval $ttl = null): bool
    {
        foreach ($values as $key => $value) {
            $this->set($key, $value, $ttl);
        }

        return true;
    }

    /**
     * @param  iterable<string>  $keys
     */
    public function deleteMultiple(iterable $keys): bool
    {
        foreach ($keys as $key) {
            $this->delete($key);
        }

        return true;
    }
}
