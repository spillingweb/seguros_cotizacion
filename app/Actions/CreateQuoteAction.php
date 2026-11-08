<?php

namespace App\Actions;

use App\DTOs\QuoteResult;
use App\Enums\InsuranceStatus;
use App\Models\Insurance;

class CreateQuoteAction
{
    public function execute(array $validated, QuoteResult $result): Insurance
    {
        return Insurance::create([
            ...$validated,
            'days_count'           => $result->daysCount,
            'base_rate'            => $result->baseRate,
            'surcharge_percentage' => $result->surchargePercentage,
            'total_amount'         => $result->totalAmount,
            'status'               => InsuranceStatus::Quoted,
        ]);
    }
}
