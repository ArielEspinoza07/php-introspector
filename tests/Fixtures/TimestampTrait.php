<?php

declare(strict_types=1);

namespace Aurora\Reflection\Tests\Fixtures;

use DateTimeImmutable;

/**
 * Trait for adding timestamp functionality
 *
 * Provides created_at and updated_at tracking for entities.
 */
trait TimestampTrait
{
    /**
     * When the entity was created
     */
    private ?DateTimeImmutable $createdAt = null;

    /**
     * When the entity was last updated
     */
    private ?DateTimeImmutable $updatedAt = null;

    /**
     * Get the creation timestamp
     *
     * @return DateTimeImmutable|null The creation date
     */
    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * Get the last update timestamp
     *
     * @return DateTimeImmutable|null The last update date
     */
    public function getUpdatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * Initialize timestamps
     *
     * @return void
     */
    protected function initializeTimestamps(): void
    {
        $this->createdAt = new DateTimeImmutable();
        $this->updatedAt = new DateTimeImmutable();
    }

    /**
     * Update the updated_at timestamp
     *
     * @return void
     */
    protected function touch(): void
    {
        $this->updatedAt = new DateTimeImmutable();
    }
}
