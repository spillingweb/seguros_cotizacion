<?php

use App\Actions\CalculateQuoteAction;
use Illuminate\Support\Carbon;

beforeEach(fn () => app()->bind(CalculateQuoteAction::class, fn () => new CalculateQuoteAction()));

it('calcula correctamente sin recargo para Sudamérica', function () {
    $action = new CalculateQuoteAction();
    $result = $action->execute(
        Carbon::parse('2025-01-01'),
        Carbon::parse('2025-01-11'),
        'South America',
    );

    expect($result->daysCount)->toBe(10)
        ->and($result->baseRate)->toBe(30.00)
        ->and($result->surchargePercentage)->toBe(0.0)
        ->and($result->totalAmount)->toBe(30.00);
});

it('aplica recargo del 20% para Europa', function () {
    $action = new CalculateQuoteAction();
    $result = $action->execute(
        Carbon::parse('2025-01-01'),
        Carbon::parse('2025-01-11'),
        'Europe',
    );

    expect($result->daysCount)->toBe(10)
        ->and($result->baseRate)->toBe(30.00)
        ->and($result->surchargePercentage)->toBe(20.0)
        ->and($result->totalAmount)->toBe(36.00);
});

it('aplica recargo del 15% para Norteamérica', function () {
    $action = new CalculateQuoteAction();
    $result = $action->execute(
        Carbon::parse('2025-01-01'),
        Carbon::parse('2025-01-11'),
        'North America',
    );

    expect($result->totalAmount)->toBe(34.50);
});

it('aplica recargo del 25% para Asia y Oceanía', function () {
    $action = new CalculateQuoteAction();

    foreach (['Asia', 'Oceania'] as $region) {
        $result = $action->execute(
            Carbon::parse('2025-01-01'),
            Carbon::parse('2025-01-11'),
            $region,
        );
        expect($result->totalAmount)->toBe(37.50);
    }
});

it('aplica recargo del 20% para África', function () {
    $action = new CalculateQuoteAction();
    $result = $action->execute(
        Carbon::parse('2025-01-01'),
        Carbon::parse('2025-01-11'),
        'Africa',
    );

    expect($result->totalAmount)->toBe(36.00);
});

it('usa 0% de recargo para regiones desconocidas', function () {
    $action = new CalculateQuoteAction();
    $result = $action->execute(
        Carbon::parse('2025-01-01'),
        Carbon::parse('2025-01-06'),
        'Antártida',
    );

    expect($result->surchargePercentage)->toBe(0.0)
        ->and($result->totalAmount)->toBe(15.00);
});
