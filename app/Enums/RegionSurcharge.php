<?php

namespace App\Enums;

enum RegionSurcharge
{
    case SouthAmerica;
    case NorthAmerica;
    case Europe;
    case Asia;
    case Africa;
    case Oceania;

    public static function rateFor(?string $region): float
    {
        return match ($region) {
            'South America' => 0.00,
            'North America' => 0.15,
            'Europe'        => 0.20,
            'Asia'          => 0.25,
            'Africa'        => 0.20,
            'Oceania'       => 0.25,
            default         => 0.00,
        };
    }
}