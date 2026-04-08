<?php

namespace Database\Seeders;

use App\Models\MCisQuestions;
use Illuminate\Database\Seeder;

class MCisQuestionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        MCisQuestions::truncate();

        $questions = [
            // PRAUJI
            ['category_id' => 1, 'sub_category_id' => 1, 'name' => 'Nomor Kendaraan', 'input_type' => 'checkbox', 'order' => 1],
            ['category_id' => 1, 'sub_category_id' => 1, 'name' => 'Nomor Uji Kendaraan', 'input_type' => 'checkbox', 'unit' => 'tahun', 'order' => 3],
            ['category_id' => 1, 'sub_category_id' => 1, 'name' => 'Nomor Landasan(Chasis)', 'input_type' => 'checkbox', 'order' => 2],
            ['category_id' => 1, 'sub_category_id' => 1, 'name' => 'Nomor Mesin', 'input_type' => 'checkbox', 'order' => 4],

            ['category_id' => 1, 'sub_category_id' => 2, 'name' => 'Lampu utama dekat tidak menyala', 'input_type' => 'checkbox', 'order' => 1],
            ['category_id' => 1, 'sub_category_id' => 2, 'name' => 'Lampu utama jauh tidak menyala', 'input_type' => 'checkbox', 'order' => 1],

            ['category_id' => 1, 'sub_category_id' => 2, 'name' => 'Lampu posisi depan tidak menyala', 'input_type' => 'checkbox', 'order' => 2],
            ['category_id' => 1, 'sub_category_id' => 2, 'name' => 'Lampu posisi belakang tidak menyala', 'input_type' => 'checkbox', 'order' => 2],

            ['category_id' => 1, 'sub_category_id' => 2, 'name' => 'Lampu penunjuk arah depan tidak menyala', 'input_type' => 'checkbox', 'order' => 3],
            ['category_id' => 1, 'sub_category_id' => 2, 'name' => 'Lampu penunjuk arah belakang tidak menyala', 'input_type' => 'checkbox', 'order' => 3],

            ['category_id' => 1, 'sub_category_id' => 2, 'name' => 'Lampu rem tidak menyala', 'input_type' => 'checkbox', 'order' => 4],
            ['category_id' => 1, 'sub_category_id' => 2, 'name' => 'Lampu mundur tidak menyala', 'input_type' => 'checkbox', 'order' => 5],
            ['category_id' => 1, 'sub_category_id' => 2, 'name' => 'Posisi / dudukan lampu utama tidak sesuai', 'input_type' => 'checkbox', 'order' => 6],
            ['category_id' => 1, 'sub_category_id' => 2, 'name' => 'Alat Pemantul Cahaya (Reflektor) Tidak Ada', 'input_type' => 'checkbox', 'order' => 7],
            ['category_id' => 1, 'sub_category_id' => 2, 'name' => 'Alat Pemantul Cahaya (Reflektor) Rusak', 'input_type' => 'checkbox', 'order' => 8],

            ['category_id' => 1, 'sub_category_id' => 3, 'name' => 'Bumper > 50 cm', 'input_type' => 'text', 'order' => 1],
            ['category_id' => 1, 'sub_category_id' => 3, 'name' => 'Kontruksi bumber membahayakan', 'input_type' => 'checkbox', 'order' => 2],
            ['category_id' => 1, 'sub_category_id' => 3, 'name' => 'Bumper belakang tidak ada', 'input_type' => 'checkbox', 'order' => 3],
            ['category_id' => 1, 'sub_category_id' => 3, 'name' => 'Kondisi bak/kabin keropos / rusak', 'input_type' => 'checkbox', 'order' => 4],
            ['category_id' => 1, 'sub_category_id' => 3, 'name' => 'Jumlah tempat duduk tidak sesuai dengan STUK/SRUT', 'input_type' => 'text', 'order' => 5],
            ['category_id' => 1, 'sub_category_id' => 3, 'name' => 'Pintu rusak', 'input_type' => 'checkbox', 'order' => 6],
            ['category_id' => 1, 'sub_category_id' => 3, 'name' => 'Tutup bak tidak ada / rusak', 'input_type' => 'checkbox', 'order' => 7],
            ['category_id' => 1, 'sub_category_id' => 3, 'name' => 'Kondisi kaca retak', 'input_type' => 'checkbox', 'order' => 8],
            ['category_id' => 1, 'sub_category_id' => 3, 'name' => 'Kondisi kaca samping retak', 'input_type' => 'checkbox', 'order' => 8],
            ['category_id' => 1, 'sub_category_id' => 3, 'name' => 'Tutup tangki bahan bakar tidak ada', 'input_type' => 'checkbox', 'order' => 8],
            ['category_id' => 1, 'sub_category_id' => 3, 'name' => 'Jenis rumah / bak tidak sesuai STUK/SRUT', 'input_type' => 'select', 'order' => 8],
        ];

        foreach ($questions as $question) {
            MCisQuestions::create($question);
        }
    }
}
