<?php

namespace Database\Seeders;

use App\Models\MCisCategories;
use App\Models\MCisSubCategories;
use Illuminate\Database\Seeder;

class MCisSubCategoriesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        MCisSubCategories::truncate();

        $categories = MCisCategories::where(function ($query) {
            $query->where('name', 'PRAUJI')->orWhere('name', 'PITLIFT');
        })->get();

        foreach ($categories as $category) {
            $category_id = $category->id;

            if ($category->name === 'PRAUJI') {
                $subCategories = [
                    ['name' => 'IDENTITAS KENDARAAN', 'order' => 1],
                    ['name' => 'SISTEM PENERANGAN', 'order' => 2],
                    ['name' => 'RUMAH DAN BODY', 'order' => 3],
                    ['name' => 'RODA', 'order' => 4],
                    ['name' => 'DIMENSI', 'order' => 5],
                    ['name' => 'PERALATAN & PERLENGKAPAN', 'order' => 6],
                ];
            } else if ($category->name === 'PITLIFT') {
                $subCategories = [
                    ['name' => 'SISTEM KEMUDI', 'order' => 1],
                    ['name' => 'LANDASAN', 'order' => 2],
                ];
            }

            // ⬇️ INSERT DI SINI (DALAM LOOP)
            foreach ($subCategories as $subCategory) {
                MCisSubCategories::create([
                    'category_id' => $category_id,
                    'name' => $subCategory['name'],
                    'order' => $subCategory['order'],
                ]);
            }
        }
    }
}
