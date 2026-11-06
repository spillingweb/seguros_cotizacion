<?php

use App\Enums\InsuranceStatus;
use App\Models\Insurance;

it('crea una cotización con datos válidos', function () {
    $payload = [
        'first_name'               => 'Juan',
        'last_name'                => 'Pérez',
        'identification_number'    => '1234567890',
        'email'                    => 'juan@example.com',
        'date_of_birth'            => '1990-05-15',
        'destination_country'      => 'España',
        'destination_country_code' => 'ES',
        'destination_region'       => 'Europe',
        'departure_date'           => now()->addDays(10)->format('Y-m-d'),
        'return_date'              => now()->addDays(20)->format('Y-m-d'),
    ];

    $response = $this->postJson('/api/quotes', $payload);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'id', 'full_name', 'destination_country', 'days_count',
            'base_rate', 'surcharge_percentage', 'total_amount', 'status',
        ])
        ->assertJsonPath('status', 'quoted')
        ->assertJsonPath('days_count', 10);
});

it('calcula el total correcto para Europa (20% recargo)', function () {
    $payload = [
        'first_name'               => 'María',
        'last_name'                => 'García',
        'identification_number'    => '0987654321',
        'email'                    => 'maria@example.com',
        'date_of_birth'            => '1985-03-20',
        'destination_country'      => 'Francia',
        'destination_country_code' => 'FR',
        'destination_region'       => 'Europe',
        'departure_date'           => now()->addDays(5)->format('Y-m-d'),
        'return_date'              => now()->addDays(15)->format('Y-m-d'),
    ];

    $response = $this->postJson('/api/quotes', $payload);

    $response->assertStatus(201)
        ->assertJsonPath('base_rate', 30)
        ->assertJsonPath('surcharge_percentage', 20)
        ->assertJsonPath('total_amount', 36);
});

it('rechaza campos faltantes', function () {
    $response = $this->postJson('/api/quotes', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors([
            'first_name', 'last_name', 'identification_number',
            'email', 'date_of_birth', 'destination_country',
            'destination_country_code', 'destination_region',
            'departure_date', 'return_date',
        ]);
});

it('rechaza fecha de salida en el pasado', function () {
    $payload = [
        'first_name'               => 'Carlos',
        'last_name'                => 'Torres',
        'identification_number'    => '1111111111',
        'email'                    => 'carlos@example.com',
        'date_of_birth'            => '1992-01-01',
        'destination_country'      => 'México',
        'destination_country_code' => 'MX',
        'destination_region'       => 'North America',
        'departure_date'           => now()->subDays(5)->format('Y-m-d'),
        'return_date'              => now()->addDays(5)->format('Y-m-d'),
    ];

    $response = $this->postJson('/api/quotes', $payload);

    $response->assertStatus(422)->assertJsonValidationErrors(['departure_date']);
});

it('rechaza fecha de regreso anterior a la de salida', function () {
    $payload = [
        'first_name'               => 'Ana',
        'last_name'                => 'Martínez',
        'identification_number'    => '2222222222',
        'email'                    => 'ana@example.com',
        'date_of_birth'            => '1995-06-10',
        'destination_country'      => 'Japón',
        'destination_country_code' => 'JP',
        'destination_region'       => 'Asia',
        'departure_date'           => now()->addDays(10)->format('Y-m-d'),
        'return_date'              => now()->addDays(5)->format('Y-m-d'),
    ];

    $response = $this->postJson('/api/quotes', $payload);

    $response->assertStatus(422)->assertJsonValidationErrors(['return_date']);
});

it('obtiene una cotización por ID', function () {
    $insurance = Insurance::factory()->create();

    $response = $this->getJson("/api/quotes/{$insurance->id}");

    $response->assertOk()->assertJsonPath('id', $insurance->id);
});
