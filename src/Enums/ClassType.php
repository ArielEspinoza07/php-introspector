<?php

declare(strict_types=1);

namespace Aurora\Reflection\Enums;

enum ClassType: string
{
    case Class_ = 'class';
    case Interface = 'interface';
    case Trait = 'trait';
    case Enum = 'enum';
    case Anonymous = 'anonymous';
}
