<?php

use App\Actions\ExportInsurancePdfAction;
use App\Enums\InsuranceStatus;
use App\Models\Insurance;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('genera y descarga el PDF de la cotización del seguro', function () {
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
    ]);

    $pdfMock = Mockery::mock(\Barryvdh\DomPDF\PDF::class);
    $pdfMock->shouldReceive('setPaper')->with('a4')->andReturnSelf();
    $pdfMock->shouldReceive('download')->andReturn(new Response('Contenido PDF simulado', 200));

    Pdf::shouldReceive('loadView')
        ->once()
        ->with('pdf.quote', ['insurance' => $insurance])
        ->andReturn($pdfMock);

    $action = new ExportInsurancePdfAction();
    $response = $action->execute($insurance);

    expect($response)->toBeInstanceOf(Response::class)
        ->and($response->getContent())->toBe('Contenido PDF simulado');
});

it('configura el tamaño de papel A4 para el PDF de cotización', function () {
    $insurance = Insurance::create([
        'first_name' => 'Ana',
        'last_name' => 'Gómez',
        'identification_number' => '789789789',
        'email' => 'ana@example.com',
        'date_of_birth' => '1992-08-10',
        'destination_country' => 'Francia',
        'destination_country_code' => 'FR',
        'destination_region' => 'Europe',
        'departure_date' => now()->addDay()->toDateString(),
        'return_date' => now()->addDays(8)->toDateString(),
        'days_count' => 7,
        'base_rate' => 21.00,
        'surcharge_percentage' => 20.0,
        'total_amount' => 25.20,
        'status' => InsuranceStatus::Quoted,
    ]);

    $pdfMock = Mockery::mock(\Barryvdh\DomPDF\PDF::class);
    // Verificamos específicamente que se llame a setPaper con 'a4'
    $pdfMock->shouldReceive('setPaper')->once()->with('a4')->andReturnSelf();
    $pdfMock->shouldReceive('download')->andReturn(new Response('PDF', 200));

    Pdf::shouldReceive('loadView')
        ->once()
        ->with('pdf.quote', ['insurance' => $insurance])
        ->andReturn($pdfMock);

    $action = new ExportInsurancePdfAction();
    $action->execute($insurance);
});