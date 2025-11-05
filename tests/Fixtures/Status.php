<?php

declare(strict_types=1);

namespace Aurora\Reflection\Tests\Fixtures;

/**
 * Status enum for testing enum reflection
 *
 * Represents various states of an entity.
 */
enum Status: string
{
    /**
     * Draft state - not yet published
     */
    case Draft = 'draft';

    /**
     * Published state - visible to public
     */
    case Published = 'published';

    /**
     * Archived state - no longer active
     */
    case Archived = 'archived';

    /**
     * Deleted state - soft deleted
     */
    case Deleted = 'deleted';

    /**
     * Default status for new entities
     */
    public const DEFAULT = self::Draft;

    /**
     * Check if the status is active
     *
     * @return bool True if active, false otherwise
     */
    public function isActive(): bool
    {
        return match ($this) {
            self::Published => true,
            default => false,
        };
    }

    /**
     * Get the status label
     *
     * @return string Human-readable label
     */
    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Published => 'Published',
            self::Archived => 'Archived',
            self::Deleted => 'Deleted',
        };
    }

    /**
     * Get all active statuses
     *
     * @return array<self> Array of active statuses
     */
    public static function actives(): array
    {
        return [self::Published];
    }
}
