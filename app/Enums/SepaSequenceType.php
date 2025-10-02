<?php

declare(strict_types=1);

namespace App\Enums;

enum SepaSequenceType: string
{
    case FIRST = 'FRST';
    case RECURRING = 'RCUR';
}
