<?php

declare(strict_types=1);

namespace Aurora\Reflection\Tests\Fixtures;

/**
 * Level 1 interface with original declarations
 */
interface Level1Interface
{
    public const LEVEL1_CONST = 'level1';

    public function level1Method(): string;
}
