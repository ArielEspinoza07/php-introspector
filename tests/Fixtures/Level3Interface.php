<?php

declare(strict_types=1);

namespace Introspector\Tests\Fixtures;

/**
 * Level 3 interface that extends Level2Interface (3 levels deep)
 */
interface Level3Interface extends Level2Interface
{
    public const LEVEL3_CONST = 'level3';
}
