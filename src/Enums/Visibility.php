<?php

declare(strict_types=1);

namespace Aurora\Reflection\Enums;

enum Visibility: string
{
    case Public = 'public';
    case Protected = 'protected';
    case Private = 'private';
}
