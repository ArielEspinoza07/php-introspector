<?php

declare(strict_types=1);

namespace Aurora\Reflection\Tests\Fixtures;

/**
 * Abstract base class for geometric shapes
 *
 * This class provides common functionality for all shapes
 * including color management and area calculation.
 *
 * @author Aurora Team
 */
abstract class AbstractShape
{
    /**
     * Default color for shapes
     */
    protected const DEFAULT_COLOR = 'black';

    /**
     * Maximum allowed area
     */
    protected const MAX_AREA = 1000.0;

    /**
     * Shape color
     */
    protected string $color;

    /**
     * Creates a new shape with the given color
     *
     * @param  string  $color  The color of the shape
     */
    public function __construct(string $color = self::DEFAULT_COLOR)
    {
        $this->color = $color;
    }

    /**
     * Calculate the area of the shape
     *
     * @return float The calculated area
     */
    abstract public function area(): float;

    /**
     * Change the shape color and return a new instance
     *
     * @param  string  $color  The new color
     * @return static A new instance with the updated color
     */
    abstract public function withColor(string $color): static;

    /**
     * Get the shape color
     *
     * @return string The color of the shape
     */
    final public function getColor(): string
    {
        return $this->color;
    }

    /**
     * Check if the shape is valid (area within limits)
     *
     * @return bool True if valid, false otherwise
     */
    final public function isValid(): bool
    {
        return $this->area() <= self::MAX_AREA;
    }
}
