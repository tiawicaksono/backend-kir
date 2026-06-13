<?php

namespace Database\Seeders;

use App\Models\MKuota;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class KuotaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        MKuota::create([
            'kuota' => 200
        ]);
    }
}
