<?php

namespace Database\Seeders;

use App\Models\ApiIntegration;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ApiIntegrationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['name' => 'Status Penerbitan', 'prefix' => 'statuspenerbitan'],
            ['name' => 'Kelas Jalan', 'prefix' => 'kelasjalan'],
            ['name' => 'Bahan Bakar', 'prefix' => 'fuel'],
            ['name' => 'Pegawai', 'prefix' => 'pegawai'],
            ['name' => 'Area', 'prefix' => 'area'],
            ['name' => 'Merk', 'prefix' => 'merk'],
            ['name' => 'Varian Merk', 'prefix' => 'variantype'],
            ['name' => 'Tipe Varian Merk', 'prefix' => 'varian'],
            ['name' => 'Jenis Kendaraan', 'prefix' => 'vehicletype'],
            ['name' => 'Sub Jenis Kendaraan', 'prefix' => 'subvehicletype'],
        ];

        foreach ($data as $item) {
            ApiIntegration::updateOrCreate(
                ['prefix' => $item['prefix']],
                $item
            );
        }
    }
}
