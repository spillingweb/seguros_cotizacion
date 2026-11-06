<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('insurances', function (Blueprint $table) {
            $table->id();

            // Asegurado
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('identification_number', 50);
            $table->string('email');
            $table->date('date_of_birth');

            // Viaje
            $table->string('destination_country', 150);
            $table->char('destination_country_code', 2);
            $table->string('destination_region', 100);
            $table->date('departure_date');
            $table->date('return_date');

            // Cotización
            $table->unsignedSmallInteger('days_count');
            $table->decimal('base_rate', 10, 2);
            $table->decimal('surcharge_percentage', 5, 2);
            $table->decimal('total_amount', 10, 2);

            // Estado
            $table->string('status', 20)->default('quoted');
            $table->timestamp('contracted_at')->nullable();

            $table->timestamps();

            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('insurances');
    }
};
