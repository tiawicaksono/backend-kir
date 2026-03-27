<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MasterProvinsiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $path = database_path('data/provinsi.csv');

        $file = fopen($path, 'r');

        fgetcsv($file); // skip header

        $data = [];

        while (($row = fgetcsv($file)) !== false) {
            [$id, $name] = $row;

            $data[] = [
                'id' => $id, // 11.01 -> 1101
                'nama_provinsi' => $name,
            ];
        }

        fclose($file);

        DB::table('master_provinsis')->upsert(
            $data,
            ['id'],
            ['nama_provinsi']
        );
    }
}
