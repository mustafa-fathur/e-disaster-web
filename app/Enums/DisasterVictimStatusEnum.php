<?php

namespace App\Enums;

enum DisasterVictimStatusEnum: string
{
    case MINOR_INJURY = 'minor_injury';
    case SERIOUS_INJURIES = 'serious_injuries';
    case LOST = 'lost';
    case DECEASED = 'deceased';
}
