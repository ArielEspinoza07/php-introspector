<?php

declare(strict_types=1);

namespace Aurora\Reflection\Tests\Fixtures;

/**
 * Class that uses nested traits (3 levels deep)
 */
trait NestedTraits
{
    use TimestampTrait;
    use TopNestedTrait;

    public string $ownPropertyNested = 'own';

    public function ownMethodNested(): string
    {
        return 'own';
    }
}
