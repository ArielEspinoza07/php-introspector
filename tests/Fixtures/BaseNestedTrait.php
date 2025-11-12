<?php

declare(strict_types=1);

namespace Aurora\Reflection\Tests\Fixtures;

/**
 * Base trait that declares original members
 */
trait BaseNestedTrait
{
    public string $baseTraitProperty = 'base';

    public function baseTraitMethod(): string
    {
        return 'base';
    }
}
