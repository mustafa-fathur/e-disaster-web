<?php

namespace App\Enums;

enum PictureTypeEnum: string
{
    case PROFILE = 'profile';
    case DISASTER = 'disaster';
    case DISASTER_REPORT = 'disaster_report';
    case DISASTER_VICTIM = 'disaster_victim';
    case DISASTER_AID = 'disaster_aid';
}
