<?php

namespace Database\Seeders;

use App\Models\Insurance;
use Illuminate\Database\Seeder;

class InsuranceSeeder extends Seeder
{
    public function run(): void
    {
        Insurance::factory()->count(20)->create();
        Insurance::factory()->contracted()->count(10)->create();
    }
}
