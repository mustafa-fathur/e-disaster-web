<?php

namespace App\Enums;

enum UserStatusEnum: string
{
    case REGISTERED = 'registered';
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
}
