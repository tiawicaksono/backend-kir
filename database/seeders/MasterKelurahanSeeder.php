<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MasterKelurahanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $path = database_path('data/kelurahan.csv');

        $file = fopen($path, 'r');

        fgetcsv($file); // skip header

        $chunkSize = 1000;
        $batch = [];

        while (($row = fgetcsv($file)) !== false) {
            [$codeRaw, $name] = $row;

            [$provinsiId, $kotaId, $kecamatanId, $kelurahanId] = explode('.', $codeRaw);

            $batch[] = [
                'id' => $provinsiId . $kotaId . $kecamatanId . $kelurahanId,
                'kecamatan_id' => $provinsiId . $kotaId . $kecamatanId,
                'nama_kelurahan' => $name,
            ];

            // 🔥 kalau sudah 1000, insert
            if (count($batch) >= $chunkSize) {
                DB::table('master_kelurahans')->upsert(
                    $batch,
                    ['id'],
                    ['nama_kelurahan', 'kecamatan_id']
                );

                $batch = []; // reset
            }
        }

        // 🔥 sisa data terakhir
        if (!empty($batch)) {
            DB::table('master_kelurahans')->upsert(
                $batch,
                ['id'],
                ['nama_kelurahan', 'kecamatan_id']
            );
        }

        fclose($file);
    }
}
