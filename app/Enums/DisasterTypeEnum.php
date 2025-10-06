<?php

namespace App\Enums;

enum DisasterTypeEnum: string
{
    case GEMPA_BUMI = 'gempa bumi';
    case TSUNAMI = 'tsunami';
    case GUNUNG_MELETUS = 'gunung meletus';
    case BANJIR = 'banjir';
    case KEKERINGAN = 'kekeringan';
    case ANGIN_TOPAN = 'angin topan';
    case TAHAN_LONGSOR = 'tahan longsor';
    case BENCANA_NON_ALAM = 'bencanan non alam';
    case BENCANA_SOSIAL = 'bencana sosial';
}
