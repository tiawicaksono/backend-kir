<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\User;
use App\Models\UserMenu;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        UserMenu::truncate();
        $operator1 = User::where('email', 'operator1@kir.com')->first();
        $menus = Menu::whereIn('code', ['rolling-alat', 'pembayaran'])->get();
        foreach ($menus as $menu) {
            UserMenu::create([
                'user_id' => $operator1->id,
                'menu_id' => $menu->id,
            ]);
        }

        $operator2 = User::where('email', 'operator2@kir.com')->first();
        $menuPembayaran = Menu::where('code', 'pembayaran')->first();
        UserMenu::create([
            'user_id' => $operator2->id,
            'menu_id' => $menuPembayaran->id,
        ]);
    }
}
