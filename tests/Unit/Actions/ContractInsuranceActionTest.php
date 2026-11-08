<?php

use App\Actions\ContractInsuranceAction;
use App\Enums\InsuranceStatus;
use App\Models\Insurance;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('actualiza el estado del seguro a contratado y asigna la fecha de contratación', function () {
    $insurance = Insurance::create([
        'first_name' => 'Jane',
        'last_name' => 'Doe',
        'identification_number' => '123456789',
        'email' => 'jane@example.com',
        'date_of_birth' => '1990-05-15',
        'destination_country' => 'Argentina',
        'destination_country_code' => 'AR',
        'destination_region' => 'South America',
        'departure_date' => now()->addDay()->toDateString(),
        'return_date' => now()->addDays(11)->toDateString(),
        'days_count' => 10,
        'base_rate' => 30.00,
        'surcharge_percentage' => 0.0,
        'total_amount' => 30.00,
        'status' => InsuranceStatus::Quoted,
        'contracted_at' => null,
    ]);

    $action = new ContractInsuranceAction();
    $result = $action->execute($insurance);

    expect($result->status)->toBe(InsuranceStatus::Contracted)
        ->and($result->contracted_at)->not->toBeNull();
});

it('mantiene la integridad si el seguro ya estaba previamente contratado', function () {
    $insurance = Insurance::create([
        'first_name' => 'John',
        'last_name' => 'Smith',
        'identification_number' => '987654321',
        'email' => 'john@example.com',
        'date_of_birth' => '1985-10-20',
        'destination_country' => 'España',
        'destination_country_code' => 'ES',
        'destination_region' => 'Europe',
        'departure_date' => now()->addDay()->toDateString(),
        'return_date' => now()->addDays(11)->toDateString(),
        'days_count' => 10,
        'base_rate' => 30.00,
        'surcharge_percentage' => 20.0,
        'total_amount' => 36.00,
        'status' => InsuranceStatus::Contracted,
        'contracted_at' => now()->subDay(),
    ]);

    $action = new ContractInsuranceAction();
    $result = $action->execute($insurance);

    expect($result->status)->toBe(InsuranceStatus::Contracted);
});