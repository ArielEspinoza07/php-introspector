<?php

declare(strict_types=1);

namespace Aurora\Reflection\Tests\Fixtures;

/**
 * Middle level trait that uses BaseNestedTrait
 */
trait MiddleNestedTrait
{
    use BaseNestedTrait;

    public string $middleTraitProperty = 'middle';

    public function middleTraitMethod(): string
    {
        return 'middle';
    }
}
