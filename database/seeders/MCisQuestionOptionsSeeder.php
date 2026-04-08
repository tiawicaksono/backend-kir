<?php

namespace Database\Seeders;

use App\Models\MCisQuestionOptions;
use Illuminate\Database\Seeder;

class MCisQuestionOptionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        MCisQuestionOptions::truncate();

        $questions = [
            // PRAUJI
            ['question_id' => 1, 'label' => 'Tidak ada'],
            ['question_id' => 1, 'label' => 'Tidak terbaca'],
            ['question_id' => 1, 'label' => 'Tidak sesuai dengan STUK/SRUT'],
            ['question_id' => 2, 'label' => 'Tidak ada'],
            ['question_id' => 2, 'label' => 'Tidak terbaca'],
            ['question_id' => 2, 'label' => 'Tidak sesuai dengan STUK/SRUT'],
            ['question_id' => 3, 'label' => 'Tidak ada'],
            ['question_id' => 3, 'label' => 'Tidak ada'],
            ['question_id' => 3, 'label' => 'Tidak terbaca'],
            ['question_id' => 4, 'label' => 'Tidak terbaca'],
            ['question_id' => 4, 'label' => 'Tidak sesuai dengan STUK/SRUT'],
            ['question_id' => 4, 'label' => 'Tidak sesuai dengan STUK/SRUT'],

            ['question_id' => 5, 'label' => 'Kiri'],
            ['question_id' => 5, 'label' => 'Kanan'],
            ['question_id' => 6, 'label' => 'Kiri'],
            ['question_id' => 6, 'label' => 'Kanan'],

            ['question_id' => 7, 'label' => 'Kiri'],
            ['question_id' => 7, 'label' => 'Kanan'],
            ['question_id' => 8, 'label' => 'Kiri'],
            ['question_id' => 8, 'label' => 'Kanan'],

            ['question_id' => 9, 'label' => 'Kiri'],
            ['question_id' => 9, 'label' => 'Kanan'],
            ['question_id' => 10, 'label' => 'Kiri'],
            ['question_id' => 10, 'label' => 'Kanan'],

            ['question_id' => 11, 'label' => 'Kiri'],
            ['question_id' => 11, 'label' => 'Kanan'],
            ['question_id' => 12, 'label' => 'Kiri'],
            ['question_id' => 12, 'label' => 'Kanan'],
            ['question_id' => 13, 'label' => 'Kiri'],
            ['question_id' => 13, 'label' => 'Kanan'],
            ['question_id' => 14, 'label' => 'Kiri'],
            ['question_id' => 14, 'label' => 'Kanan'],
            ['question_id' => 15, 'label' => 'Kiri'],
            ['question_id' => 15, 'label' => 'Kanan'],

            ['question_id' => 23, 'label' => 'Depan'],
            ['question_id' => 23, 'label' => 'Belakang'],
            ['question_id' => 24, 'label' => 'Kiri'],
            ['question_id' => 24, 'label' => 'Kanan'],
        ];

        foreach ($questions as $question) {
            MCisQuestionOptions::create($question);
        }
    }
}
