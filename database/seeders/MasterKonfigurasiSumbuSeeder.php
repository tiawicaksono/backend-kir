<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MasterKonfigurasiSumbuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $materials = [
            '1.1',
            '1.1.2',
            '1.2',
            '1.2.2',
            '1.1.2.2',
            '2.2',
            '2.2.2',
            '1.1.1',
            '1.2.1'
        ];

        $data = array_map(function ($item) {
            return [
                'name' => $item,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }, $materials);

        DB::table('master_konfigurasi_sumbus')->upsert($data, ['name'], ['updated_at']);
    }
}
