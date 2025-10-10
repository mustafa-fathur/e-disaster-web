<?php

namespace App\Enums;

enum DisasterStatusEnum: string
{
    case CANCELLED = 'cancelled';
    case ONGOING = 'ongoing';
    case COMPLETED = 'completed';
}
