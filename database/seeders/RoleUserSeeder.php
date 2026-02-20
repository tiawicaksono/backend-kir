<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /**
         * Role ID 1: Admin
         * Role ID 2: Operator
         * Role ID 3: Penguji
         */
        $assignments = [
            1 => [1],        // User 1 → Role 1
            2 => [1, 3],     // User 2 → Role 1 & 2
            3 => [2],        // User 3 → Role 2
            4 => [2],        // User 4 → Role 2
            5 => [3],        // User 5 → Role 3
            6 => [3],        // User 6 → Role 3
        ];

        foreach ($assignments as $userId => $roleIds) {
            $user = User::find($userId);

            if ($user) {
                $user->roles()->sync($roleIds);
            }
        }
    }
}
