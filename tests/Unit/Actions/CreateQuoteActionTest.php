<?php

use App\Actions\CreateQuoteAction;
use App\DTOs\QuoteResult;
use App\Enums\InsuranceStatus;
use App\Models\Insurance;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('crea un registro de seguro con los datos validados y los cálculos de la cotización', function () {
    $validatedData = [
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
    ];

    $quoteResult = new QuoteResult(
        daysCount: 10,
        baseRate: 30.00,
        surchargePercentage: 0.0,
        totalAmount: 30.00,
    );

    $action = new CreateQuoteAction();
    $insurance = $action->execute($validatedData, $quoteResult);

    expect($insurance)->toBeInstanceOf(Insurance::class)
        ->and($insurance->first_name)->toBe('Jane')
        ->and((float) $insurance->days_count)->toBe(10.0);

    $this->assertDatabaseHas('insurances', [
        'id' => $insurance->id,
        'email' => 'jane@example.com',
    ]);
});

it('asigna el estado Quoted por defecto al crear una cotización', function () {
    $validatedData = [
        'first_name' => 'Carlos',
        'last_name' => 'Pérez',
        'identification_number' => '456456456',
        'email' => 'carlos@example.com',
        'date_of_birth' => '1995-02-20',
        'destination_country' => 'Chile',
        'destination_country_code' => 'CL',
        'destination_region' => 'South America',
        'departure_date' => now()->addDay()->toDateString(),
        'return_date' => now()->addDays(5)->toDateString(),
    ];

    $quoteResult = new QuoteResult(
        daysCount: 4,
        baseRate: 12.00,
        surchargePercentage: 0.0,
        totalAmount: 12.00,
    );

    $action = new CreateQuoteAction();
    $insurance = $action->execute($validatedData, $quoteResult);

    expect($insurance->status)->toBe(InsuranceStatus::Quoted);
});