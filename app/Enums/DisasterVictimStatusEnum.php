<?php

namespace App\Enums;

enum DisasterVictimStatusEnum: string
{
    case LUKA_RINGAN = 'luka ringan';
    case LUKA_BERAT = 'luka berat';
    case MENINGGAL = 'meninggal';
    case HILANG = 'hilang';
}
