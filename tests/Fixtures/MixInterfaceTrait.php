<?php

namespace Aurora\Reflection\Tests\Fixtures;

trait MixInterfaceTrait
{
    public function level1Method(): string
    {
        return 'level1';
    }

    public function level2Method(): string
    {
        return 'level2';
    }

    public function ownMethod(): string
    {
        return 'own';
    }
}
