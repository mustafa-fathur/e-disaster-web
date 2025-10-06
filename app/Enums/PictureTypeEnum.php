<?php

namespace App\Enums;

enum PictureTypeEnum: string
{
    case PROFILE = 'profile';
    case DISASTER = 'disaster';
    case REPORT = 'report';
    case VICTIM = 'victim';
    case AID = 'aid';
}
