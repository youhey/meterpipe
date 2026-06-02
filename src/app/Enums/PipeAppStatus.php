<?php

namespace App\Enums;

enum PipeAppStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Planned = 'planned';
}
