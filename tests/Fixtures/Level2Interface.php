<?php

declare(strict_types=1);

namespace Aurora\Reflection\Tests\Fixtures;

/**
 * Level 2 interface that extends Level1Interface
 */
interface Level2Interface extends Level1Interface
{
    public const LEVEL2_CONST = 'level2';

    public function level2Method(): string;
}
