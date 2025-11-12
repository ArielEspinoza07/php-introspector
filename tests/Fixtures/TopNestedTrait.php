<?php

declare(strict_types=1);

namespace Aurora\Reflection\Tests\Fixtures;

/**
 * Top level trait that uses MiddleNestedTrait (3 levels deep)
 */
trait TopNestedTrait
{
    use MiddleNestedTrait;

    public string $topTraitProperty = 'top';

    public function topTraitMethod(): string
    {
        return 'top';
    }
}
