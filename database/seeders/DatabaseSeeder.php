<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $this->call([
            RoleSeeder::class,        // 1️⃣ roles
            MenuSeeder::class,        // 2️⃣ menus
            RoleMenuSeeder::class,    // 3️⃣ role_menu (butuh role + menu)
            UserSeeder::class,        // 4️⃣ users (butuh role)
            UserMenuSeeder::class,    // 5️⃣ user_menu (butuh user + menu)
            RoleUserSeeder::class,    // 6️⃣ role_user (butuh user + role)
            ApiTokenSeeder::class,
            ApiIntegrationSeeder::class
        ]);
    }
}
