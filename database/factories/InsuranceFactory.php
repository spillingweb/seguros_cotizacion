<?php

namespace Database\Factories;

use App\Actions\CalculateQuoteAction;
use App\Enums\InsuranceStatus;
use App\Models\Insurance;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class InsuranceFactory extends Factory
{
    protected $model = Insurance::class;

    private const array DESTINATIONS = [
        ['name' => 'España',           'code' => 'ES', 'region' => 'Europe'],
        ['name' => 'Francia',          'code' => 'FR', 'region' => 'Europe'],
        ['name' => 'Italia',           'code' => 'IT', 'region' => 'Europe'],
        ['name' => 'Alemania',         'code' => 'DE', 'region' => 'Europe'],
        ['name' => 'Portugal',         'code' => 'PT', 'region' => 'Europe'],
        ['name' => 'Estados Unidos',   'code' => 'US', 'region' => 'North America'],
        ['name' => 'Canadá',           'code' => 'CA', 'region' => 'North America'],
        ['name' => 'México',           'code' => 'MX', 'region' => 'North America'],
        ['name' => 'Colombia',         'code' => 'CO', 'region' => 'South America'],
        ['name' => 'Perú',             'code' => 'PE', 'region' => 'South America'],
        ['name' => 'Chile',            'code' => 'CL', 'region' => 'South America'],
        ['name' => 'Argentina',        'code' => 'AR', 'region' => 'South America'],
        ['name' => 'Brasil',           'code' => 'BR', 'region' => 'South America'],
        ['name' => 'Japón',            'code' => 'JP', 'region' => 'Asia'],
        ['name' => 'Tailandia',        'code' => 'TH', 'region' => 'Asia'],
        ['name' => 'China',            'code' => 'CN', 'region' => 'Asia'],
        ['name' => 'Australia',        'code' => 'AU', 'region' => 'Oceania'],
        ['name' => 'Nueva Zelanda',    'code' => 'NZ', 'region' => 'Oceania'],
        ['name' => 'Marruecos',        'code' => 'MA', 'region' => 'Africa'],
        ['name' => 'Sudáfrica',        'code' => 'ZA', 'region' => 'Africa'],
    ];

    private const array SPANISH_FIRST_NAMES = [
        'Carlos', 'María', 'José', 'Ana', 'Luis', 'Laura', 'Miguel', 'Carmen',
        'Jorge', 'Isabel', 'Andrés', 'Sofía', 'Diego', 'Valentina', 'Sebastián',
        'Lucía', 'Alejandro', 'Gabriela', 'Rodrigo', 'Daniela',
    ];

    private const array SPANISH_LAST_NAMES = [
        'García', 'Rodríguez', 'González', 'Fernández', 'López', 'Martínez',
        'Sánchez', 'Pérez', 'Gómez', 'Torres', 'Flores', 'Rivera', 'Vargas',
        'Castro', 'Jiménez', 'Morales', 'Ortiz', 'Herrera', 'Medina', 'Ramos',
    ];

    public function definition(): array
    {
        $destination = $this->faker->randomElement(self::DESTINATIONS);
        $departureDate = Carbon::instance($this->faker->dateTimeBetween('-1 month', '+3 months'));
        $returnDate = Carbon::instance($this->faker->dateTimeBetween(
            $departureDate->copy()->addDays(3),
            $departureDate->copy()->addDays(30),
        ));

        $result = (new CalculateQuoteAction())->execute($departureDate, $returnDate, $destination['region']);

        $firstName = $this->faker->randomElement(self::SPANISH_FIRST_NAMES);
        $lastName = $this->faker->randomElement(self::SPANISH_LAST_NAMES)
            . ' ' . $this->faker->randomElement(self::SPANISH_LAST_NAMES);

        return [
            'first_name'               => $firstName,
            'last_name'                => $lastName,
            'identification_number'    => $this->faker->numerify('##########'),
            'email'                    => $this->faker->unique()->safeEmail(),
            'date_of_birth'            => $this->faker->dateTimeBetween('-70 years', '-18 years')->format('Y-m-d'),
            'destination_country'      => $destination['name'],
            'destination_country_code' => $destination['code'],
            'destination_region'       => $destination['region'],
            'departure_date'           => $departureDate->format('Y-m-d'),
            'return_date'              => $returnDate->format('Y-m-d'),
            'days_count'               => $result->daysCount,
            'base_rate'                => $result->baseRate,
            'surcharge_percentage'     => $result->surchargePercentage,
            'total_amount'             => $result->totalAmount,
            'status'                   => InsuranceStatus::Quoted,
            'contracted_at'            => null,
        ];
    }

    public function contracted(): static
    {
        return $this->state(fn () => [
            'status'        => InsuranceStatus::Contracted,
            'contracted_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
        ]);
    }
}
