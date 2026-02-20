<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    /**
     * ================= HELPER =================
     */
    private function createSubMenu(
        Menu $parent,
        string $code,
        string $icon,
        string $route,
        int $order,
        string $id,
        string $en
    ): void {
        $menu = Menu::create([
            'code' => $code,
            'icon' => $icon,
            'parent_id' => $parent->id,
            'route' => $route,
            'order' => $order,
        ]);

        $menu->translations()->createMany([
            ['locale' => 'id', 'name' => $id],
            ['locale' => 'en', 'name' => $en],
        ]);
    }
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Menu::truncate();

        $dashboard = Menu::create([
            'code' => 'dashboard',
            'icon' => 'dashboard',
            'route' => '/dashboard',
            'order' => 1
        ]);

        $dashboard->translations()->createMany([
            [
                'locale' => 'id',
                'name' => 'Dashboard'
            ],
            [
                'locale' => 'en',
                'name' => 'Dashboard'
            ]
        ]);

        $loket = Menu::create([
            'code' => 'pendaftaran',
            'icon' => 'shopping-cart',
            'order' => 2,
        ]);

        $loket->translations()->createMany([
            [
                'locale' => 'id',
                'name' => 'Pendaftaran'
            ],
            [
                'locale' => 'en',
                'name' => 'Registration'
            ]
        ]);

        /**
         * Sub Menu Loket
         */
        $this->createSubMenu(
            parent: $loket,
            code: 'daftar-uji',
            icon: '',
            route: '/loket/daftar-uji',
            order: 1,
            id: 'Daftar Uji',
            en: 'Test Registration'
        );

        $this->createSubMenu(
            parent: $loket,
            code: 'daftar-uji-ulang',
            icon: '',
            route: '/loket/daftar-uji-ulang',
            order: 2,
            id: 'Daftar Uji Ulang',
            en: 'Re-Test Registration'
        );

        $this->createSubMenu(
            parent: $loket,
            code: 'pembayaran',
            icon: '',
            route: '/loket/pembayaran',
            order: 3,
            id: 'Pembayaran',
            en: 'Payment'
        );

        $this->createSubMenu(
            parent: $loket,
            code: 'cetak-kartu-uji',
            icon: '',
            route: '/loket/cetak-kartu-uji',
            order: 4,
            id: 'Cetak Kartu Uji',
            en: 'Print Test Card'
        );

        $this->createSubMenu(
            parent: $loket,
            code: 'rekom',
            icon: '',
            route: '/loket/rekom',
            order: 5,
            id: 'Surat Rekomendasi',
            en: 'Recommendation'
        );

        $this->createSubMenu(
            parent: $loket,
            code: 'riwayat-kendaraan',
            icon: '',
            route: '/loket/riwayat-kendaraan',
            order: 6,
            id: 'Riwayat Kendaraan',
            en: 'Vehicle History'
        );
        // =====================================================================


        $master = Menu::create([
            'code' => 'data-master',
            'icon' => 'chart',
            'order' => 3,
        ]);

        $master->translations()->createMany([
            [
                'locale' => 'id',
                'name' => 'Data Master'
            ],
            [
                'locale' => 'en',
                'name' => 'Master Data'
            ]
        ]);

        /**
         * Sub Menu Master
         */
        $this->createSubMenu(
            parent: $master,
            code: 'data-kendaraan',
            icon: '',
            route: '/master/data-kendaraan',
            order: 1,
            id: 'Data Kendaraan',
            en: 'Vehicle Data'
        );

        $this->createSubMenu(
            parent: $master,
            code: 'rolling-alat',
            icon: '',
            route: '/master/rolling-alat',
            order: 2,
            id: 'Jadwal Penguji',
            en: 'Schedule of Testers'
        );

        $this->createSubMenu(
            parent: $master,
            code: 'api-kementrian',
            icon: '',
            route: '/master/api-kementrian',
            order: 3,
            id: 'API Kementrian',
            en: 'Ministry API'
        );
        // =====================================================================


        $pengujian = Menu::create([
            'code' => 'pengujian',
            'route' => '/pengujian',
            'order' => 4,
        ]);

        $pengujian->translations()->createMany([
            [
                'locale' => 'id',
                'name' => 'Pengujian'
            ],
            [
                'locale' => 'en',
                'name' => 'Testing'
            ]
        ]);

        $report = Menu::create([
            'code' => 'laporan',
            'icon' => 'file-text',
            'order' => 5,
        ]);

        $report->translations()->createMany([
            [
                'locale' => 'id',
                'name' => 'Laporan'
            ],
            [
                'locale' => 'en',
                'name' => 'Reports'
            ]
        ]);

        /**
         * Sub Menu Report
         */
        $this->createSubMenu(
            parent: $report,
            code: 'kendaraan-uji',
            icon: '',
            route: '/report/kendaraan-uji',
            order: 1,
            id: 'Kendaraan Uji',
            en: 'Vehicle Testing'
        );

        $this->createSubMenu(
            parent: $report,
            code: 'kendaraan-per-kecamatan',
            icon: '',
            route: '/report/kendaraan-per-kecamatan',
            order: 2,
            id: 'Kendaraan per Kecamatan',
            en: 'Vehicles by District'
        );
        // =====================================================================

        $ubahPassword = Menu::create([
            'code' => 'ubah-password',
            'route' => '/ubah-password',
            'order' => 6,
        ]);

        $ubahPassword->translations()->createMany([
            [
                'locale' => 'id',
                'name' => 'Ubah Password',
            ],
            [
                'locale' => 'en',
                'name' => 'Change Password',
            ]
        ]);
    }
}
