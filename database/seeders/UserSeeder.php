<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::truncate();
        $admin = Role::where('name', 'admin')->first();
        User::insert([
            [
                'name' => 'Admin',
                'phone' => '085755124531',
                'email' => 'admin@kir.com',
                'password' => bcrypt('password'),
                'role_id' => $admin->id
            ],
            [
                'name' => 'Tia Wicaksono',
                'phone' => '085755124535',
                'email' => 'wicaksono.tia@gmail.com',
                'password' => bcrypt('password'),
                'role_id' => $admin->id
            ]
        ]);

        $staff = Role::where('name', 'operator')->first();
        User::insert([
            [
                'name' => 'Operator 1',
                'phone' => '085755124532',
                'email' => 'operator1@kir.com',
                'password' => bcrypt('password'),
                'role_id' => $staff->id
            ],
            [
                'name' => 'Operator 2',
                'phone' => '085755124533',
                'email' => 'operator2@kir.com',
                'password' => bcrypt('password'),
                'role_id' => $staff->id
            ]
        ]);

        $penguji = Role::where('name', 'penguji')->first();
        User::insert([
            [
                'name' => 'Penguji 1',
                'phone' => '085755124534',
                'email' => 'penguji1@kir.com',
                'password' => bcrypt('password'),
                'role_id' => $penguji->id
            ],
            [
                'name' => 'Penguji 2',
                'phone' => '085755124536',
                'email' => 'penguji2@kir.com',
                'password' => bcrypt('password'),
                'role_id' => $penguji->id
            ]
        ]);
    }
}
