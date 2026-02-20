<?php

namespace Database\Seeders;

use App\Models\Menu;
use App\Models\Role;
use App\Models\RoleMenu;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        RoleMenu::truncate();

        // Assign all menus to operator role
        $operator = Role::where('name', 'operator')->first();
        $menus = Menu::whereNotIn('code', ['rolling-alat', 'pembayaran'])->get();
        foreach ($menus as $menu) {
            RoleMenu::create([
                'role_id' => $operator->id,
                'menu_id' => $menu->id,
                'can_view' => true,
                'can_create' => true,
                'can_update' => true,
                'can_delete' => true
            ]);
        }
    }
}
