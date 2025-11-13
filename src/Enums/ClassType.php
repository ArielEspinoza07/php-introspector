<?php

declare(strict_types=1);

namespace Introspector\Enums;

enum ClassType: string
{
    case Class_ = 'class';
    case Interface_ = 'interface';
    case Trait_ = 'trait';
    case Enum_ = 'enum';
    case Anonymous_ = 'anonymous';
}
