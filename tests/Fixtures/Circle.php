<?php

declare(strict_types=1);

namespace Introspector\Tests\Fixtures;

use Override;

/**
 * Circle shape implementation
 *
 * Represents a circular geometric shape with a radius.
 */
final class Circle extends AbstractShape
{
    /**
     * Mathematical constant PI
     */
    public const PI = 3.14159;

    /**
     * Creates a new circle
     *
     * @param  float  $radius  The radius of the circle
     * @param  string  $color  The color of the circle
     */
    public function __construct(
        private float $radius,
        string $color = parent::DEFAULT_COLOR
    ) {
        parent::__construct($color);
    }

    /**
     * Calculate the circle area (πr²)
     *
     * @return float The area of the circle
     */
    #[Override]
    public function area(): float
    {
        return self::PI * $this->radius * $this->radius;
    }

    /**
     * Get the radius
     *
     * @return float The circle radius
     */
    public function getRadius(): float
    {
        return $this->radius;
    }

    /**
     * Create a new circle with different color
     *
     * @param  string  $color  The new color
     * @return static A new circle instance
     */
    #[Override]
    public function withColor(string $color): static
    {
        return new self($this->radius, $color);
    }

    /**
     * Create a new circle with different radius
     *
     * @param  float  $radius  The new radius
     * @return self A new circle instance
     */
    public function withRadius(float $radius): self
    {
        return new self($radius, $this->color);
    }

    /**
     * Get the parent shape instance (for testing parent type)
     *
     * @return parent|null The parent shape or null
     */
    public function getParent(): ?parent
    {
        return null;
    }
}
