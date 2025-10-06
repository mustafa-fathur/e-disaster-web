<?php

namespace App\Enums;

enum UserTypeEnum: string
{
    case ADMIN = 'admin';
    case OFFICER = 'officer';
    case VOLUNTEER = 'volunteer';
}
