<?php

declare(strict_types=1);

namespace Aurora\Reflection\Enums;

enum SourceType: string
{
    case Self_ = 'self';
    case Parent_ = 'parent';
    case Interface_ = 'interface';
    case Trait_ = 'trait';
}
