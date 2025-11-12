<?php

declare(strict_types=1);

namespace Aurora\Reflection\Tests\Fixtures;

/**
 * Class that implements nested interfaces (3 levels deep)
 */
interface NestedInterfaces extends Level3Interface
{
    public const ownProperty = 'own';

    public function ownMethod(): string;
}
