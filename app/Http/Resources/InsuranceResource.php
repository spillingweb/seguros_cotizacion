<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InsuranceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                       => $this->id,
            'first_name'               => $this->first_name,
            'last_name'                => $this->last_name,
            'full_name'                => $this->full_name,
            'identification_number'    => $this->identification_number,
            'email'                    => $this->email,
            'date_of_birth'            => $this->date_of_birth->format('Y-m-d'),
            'destination_country'      => $this->destination_country,
            'destination_country_code' => $this->destination_country_code,
            'destination_region'       => $this->destination_region,
            'departure_date'           => $this->departure_date->format('Y-m-d'),
            'return_date'              => $this->return_date->format('Y-m-d'),
            'days_count'               => $this->days_count,
            'base_rate'                => (float) $this->base_rate,
            'surcharge_percentage'     => (float) $this->surcharge_percentage,
            'total_amount'             => (float) $this->total_amount,
            'status'                   => $this->status->value,
            'status_label'             => $this->status->label(),
            'contracted_at'            => $this->contracted_at?->toIso8601String(),
            'created_at'               => $this->created_at->toIso8601String(),
        ];
    }
}
