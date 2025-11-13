<?php

declare(strict_types=1);

namespace Introspector\Enums;

enum Visibility: string
{
    case Public = 'public';
    case Protected = 'protected';
    case Private = 'private';
}
