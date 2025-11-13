<?php

declare(strict_types=1);

namespace Introspector\Tests\Fixtures;

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
