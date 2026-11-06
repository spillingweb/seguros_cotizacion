<?php

use App\Enums\InsuranceStatus;
use App\Models\Insurance;

it('lista las contrataciones paginadas', function () {
    Insurance::factory()->count(5)->create();

    $response = $this->getJson('/api/insurances');

    $response->assertOk()
        ->assertJsonStructure([
            'data' => [['id', 'full_name', 'destination_country', 'status']],
            'meta' => ['current_page', 'total'],
        ]);
});

it('busca contrataciones por nombre', function () {
    Insurance::factory()->create(['first_name' => 'UnicoNombre', 'last_name' => 'Test']);
    Insurance::factory()->count(3)->create();

    $response = $this->getJson('/api/insurances?search=UnicoNombre');

    $response->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.first_name', 'UnicoNombre');
});

it('filtra por estado', function () {
    Insurance::factory()->count(3)->create(['status' => InsuranceStatus::Quoted]);
    Insurance::factory()->contracted()->count(2)->create();

    $response = $this->getJson('/api/insurances?status=contracted');

    $response->assertOk()
        ->assertJsonCount(2, 'data');
});

it('contrata un seguro cotizado', function () {
    $insurance = Insurance::factory()->create(['status' => InsuranceStatus::Quoted]);

    $response = $this->postJson("/api/quotes/{$insurance->id}/contract");

    $response->assertOk()
        ->assertJsonPath('status', 'contracted');

    expect($insurance->fresh()->isContracted())->toBeTrue();
});

it('no permite contratar dos veces', function () {
    $insurance = Insurance::factory()->contracted()->create();

    $response = $this->postJson("/api/quotes/{$insurance->id}/contract");

    $response->assertStatus(422);
});

it('devuelve 404 para seguro inexistente', function () {
    $response = $this->getJson('/api/insurances/99999');

    $response->assertNotFound();
});
