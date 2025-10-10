<?php

namespace App\Enums;

enum DisasterTypeEnum: string
{
    case EARTHQUAKE = 'earthquake';
    case TSUNAMI = 'tsunami';
    case VOLCANIC_ERUPTION = 'volcanic_eruption';
    case FLOOD = 'flood';
    case DROUGHT = 'drought';
    case TORNADO = 'tornado';
    case LANDSLIDE = 'landslide';
    case NON_NATURAL_DISASTER = 'non_natural_disaster';
    case SOCIAL_DISASTER = 'social_disaster';
}
