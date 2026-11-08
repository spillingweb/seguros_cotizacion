<?php

namespace App\Actions;

use App\Enums\InsuranceStatus;
use App\Models\Insurance;

class ContractInsuranceAction
{
    public function execute(Insurance $insurance): Insurance
    {
        $insurance->update([
            'status'        => InsuranceStatus::Contracted,
            'contracted_at' => now(),
        ]);

        return $insurance->fresh();
    }
}