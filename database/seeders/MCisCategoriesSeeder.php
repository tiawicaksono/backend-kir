<?php

namespace Database\Seeders;

use App\Models\MCisCategories;
use Illuminate\Database\Seeder;

class MCisCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        MCisCategories::truncate();

        $categories = [
            ['name' => 'PRAUJI', 'order' => 1],
            ['name' => 'EMISI', 'order' => 2],
            ['name' => 'SPEEDOMETER & KLAKSON', 'order' => 3],
            ['name' => 'PITLIFT', 'order' => 4],
            ['name' => 'LAMPU', 'order' => 5],
            ['name' => 'REM', 'order' => 6],
        ];

        foreach ($categories as $category) {
            MCisCategories::create($category);
        }
    }
}
