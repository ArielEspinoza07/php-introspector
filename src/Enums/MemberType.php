<?php

declare(strict_types=1);

namespace Aurora\Reflection\Enums;

enum MemberType: string
{
    case Property = 'property';
    case Method = 'method';
    case Constant = 'constant';
}
