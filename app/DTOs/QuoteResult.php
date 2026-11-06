<?php

namespace App\DTOs;

final readonly class QuoteResult
{
    public function __construct(
        public int $daysCount,
        public float $baseRate,
        public float $surchargePercentage,
        public float $totalAmount,
    ) {}
}
