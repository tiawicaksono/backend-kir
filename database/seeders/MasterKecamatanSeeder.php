<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MasterKecamatanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $path = database_path('data/kecamatan.csv');

        $file = fopen($path, 'r');

        fgetcsv($file); // skip header

        $data = [];

        while (($row = fgetcsv($file)) !== false) {
            [$codeRaw, $name] = $row;

            [$provinsiId, $kotaId, $kecamatanId] = explode('.', $codeRaw);

            $data[] = [
                'id' => $provinsiId . $kotaId . $kecamatanId,
                'kota_id' => $provinsiId . $kotaId,
                'nama_kecamatan' => $name,
            ];
        }

        fclose($file);

        DB::table('master_kecamatans')->upsert(
            $data,
            ['id'],
            ['nama_kecamatan', 'kota_id']
        );
    }
}
