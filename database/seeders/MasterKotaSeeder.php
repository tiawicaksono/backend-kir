<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MasterKotaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $path = database_path('data/kabupaten_kota.csv');

        $file = fopen($path, 'r');

        fgetcsv($file); // skip header

        $data = [];

        while (($row = fgetcsv($file)) !== false) {
            [$codeRaw, $name] = $row;

            [$provinsiId, $kotaId] = explode('.', $codeRaw);

            $data[] = [
                'id' => $provinsiId . $kotaId, // 11.01 -> 1101
                'provinsi_id' => $provinsiId,
                'nama_kota' => $name,
            ];
        }

        fclose($file);

        DB::table('master_kotas')->upsert(
            $data,
            ['id'],
            ['nama_kota', 'provinsi_id']
        );
    }
}
