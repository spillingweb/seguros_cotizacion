<?php

namespace App\Actions;

use App\DTOs\QuoteResult;
use App\Enums\RegionSurcharge;
use Illuminate\Support\Carbon;

class CalculateQuoteAction
{
    private const float BASE_RATE_PER_DAY = 3.00;

    public function execute(Carbon $departureDate, Carbon $returnDate, string $region): QuoteResult
    {
        $days = (int) $departureDate->diffInDays($returnDate);
        $baseRate = $days * self::BASE_RATE_PER_DAY;
        $surchargePercentage = RegionSurcharge::rateFor($region);
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
