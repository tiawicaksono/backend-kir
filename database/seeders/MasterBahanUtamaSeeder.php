<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MasterBahanUtamaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $materials = [
            '-',
            'ALUMINIUM',
            'ALUMINIUM-FIBERGLASS',
            'BESI-ALUMINIUM',
            'BESI-FIBERGLASS',
            'BESI-RAM',
            'BESI-STAINLESS STEEL',
            'COMPOSITE',
            'COMPOSITE-ALUMINIUM',
            'FIBERGLASS',
            'KAYU',
            'KAYU-ALUMINIUM',
            'KAYU-BESI',
            'KAYU-PIPA',
            'KAYU-RAM',
            'FIBERGLASS-ALUMINIUM',
            'BESI-PIPA',
            'STAINLESS STEEL',
            'KAYU-TERPAL',
            'BESI-KAYU',
            'BESI-TERPAL',
            'COMPOSITE-FIBERGLASS',
            'BESI PLAT',
        ];

        $data = array_map(function ($item) {
            return [
                'bahan_utama' => $item,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }, $materials);

        DB::table('master_bahan_utamas')->insert($data);
    }
}
