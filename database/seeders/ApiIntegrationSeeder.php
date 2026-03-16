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
            ['name' => 'Status Penerbitan', 'prefix' => 'statuspenerbitan', 'description' => 'Connect Mailchimp to streamline your email marketing—automate campaigns.'],
            ['name' => 'Kelas Jalan', 'prefix' => 'kelasjalan', 'description' => 'Connect your Google Meet account for seamless video conferencing.'],
            ['name' => 'Bahan Bakar', 'prefix' => 'fuel', 'description' => 'Integrate Zoom to streamline your virtual meetings and team collaborations.'],
            ['name' => 'Pegawai', 'prefix' => 'pegawai', 'description' => 'Integrate Loom to easily record, share, and manage video messages.'],
            ['name' => 'Area', 'prefix' => 'area', 'description' => 'Connect Mailchimp to streamline your email marketing—automate campaigns.'],
            ['name' => 'Merk', 'prefix' => 'merk', 'description' => 'Connect your Google Meet account for seamless video conferencing.'],
            ['name' => 'Varian Merk', 'prefix' => 'variantype', 'description' => 'Integrate Zoom to streamline your virtual meetings and team collaborations.'],
            ['name' => 'Tipe Varian Merk', 'prefix' => 'varian', 'description' => 'Connect Mailchimp to streamline your email marketing—automate campaigns.'],
            ['name' => 'Jenis Kendaraan', 'prefix' => 'vehicletype', 'description' => 'Connect Mailchimp to streamline your email marketing—automate campaigns.'],
            ['name' => 'Sub Jenis Kendaraan', 'prefix' => 'subvehicletype', 'description' => 'Connect your Google Meet account for seamless video conferencing.'],
        ];

        foreach ($data as $item) {
            ApiIntegration::updateOrCreate(
                ['prefix' => $item['prefix']],
                $item
            );
        }
    }
}
