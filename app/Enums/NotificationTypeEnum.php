<?php

namespace App\Enums;

enum NotificationTypeEnum: string
{
    case VOLUNTEER_VERIFICATION = 'volunteer_verification';
    case NEW_DISASTER = 'new_disaster';
    case NEW_DISASTER_REPORT = 'new_disaster_report';
    case NEW_DISASTER_VICTIM_REPORT = 'new_disaster_victim_report';
    case NEW_DISASTER_AID_REPORT = 'new_disaster_aid_report';
    case DISASTER_STATUS_CHANGED = 'disaster_status_changed';
}
