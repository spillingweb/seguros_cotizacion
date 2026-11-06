<?php

namespace App\Actions;

use App\DTOs\QuoteResult;
use Illuminate\Support\Carbon;

class CalculateQuoteAction
{
    private const float BASE_RATE_PER_DAY = 3.00;

    private const array REGION_SURCHARGES = [
        'South America' => 0.00,
        'North America' => 0.15,
        'Europe'        => 0.20,
        'Asia'          => 0.25,
        'Africa'        => 0.20,
        'Oceania'       => 0.25,
    ];

    public function execute(Carbon $departureDate, Carbon $returnDate, string $region): QuoteResult
    {
        $days = (int) $departureDate->diffInDays($returnDate);
        $baseRate = $days * self::BASE_RATE_PER_DAY;
        $surchargePercentage = self::REGION_SURCHARGES[$region] ?? 0.00;
        $surchargeAmount = $baseRate * $surchargePercentage;
        $totalAmount = $baseRate + $surchargeAmount;

        return new QuoteResult(
            daysCount: $days,
            baseRate: round($baseRate, 2),
            surchargePercentage: $surchargePercentage * 100,
            totalAmount: round($totalAmount, 2),
        );
    }
}
