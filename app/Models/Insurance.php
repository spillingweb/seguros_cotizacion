<?php

namespace App\Models;

use App\Enums\InsuranceStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Insurance extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'identification_number',
        'email',
        'date_of_birth',
        'destination_country',
        'destination_country_code',
        'destination_region',
        'departure_date',
        'return_date',
        'days_count',
        'base_rate',
        'surcharge_percentage',
        'total_amount',
        'status',
        'contracted_at',
    ];

    protected $casts = [
        'date_of_birth'        => 'date',
        'departure_date'       => 'date',
        'return_date'          => 'date',
        'base_rate'            => 'decimal:2',
        'surcharge_percentage' => 'decimal:2',
        'total_amount'         => 'decimal:2',
        'contracted_at'        => 'datetime',
        'status'               => InsuranceStatus::class,
    ];

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    public function isContracted(): bool
    {
        return $this->status === InsuranceStatus::Contracted;
    }
}
