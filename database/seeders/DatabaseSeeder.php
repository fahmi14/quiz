<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Quiz;
use App\Models\Question;
use App\Models\Participant;
use App\Models\ParticipantAnswer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Admin default
        $admin = User::firstOrCreate(
            ['email' => 'admin@quiz.test'],
            [
                'name' => 'Admin Pastel Quiz',
                'username' => 'admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
            ]
        );

        // 2. Demo Quiz
        $quiz = Quiz::create([
            'title' => 'Wawasan Nusantara & Pengetahuan Umum',
            'code' => 'NUSANTARA26',
            'description' => 'Kuis seru interaktif seputar sejarah, kebudayaan, geografi, dan ilmu pengetahuan dengan nuansa pastel kalem.',
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addDays(7),
            'duration_minutes' => 15,
            'shuffle_questions' => true,
            'shuffle_options' => true,
            'is_active' => true,
            'created_by' => $admin->id,
        ]);

        $questionsData = [
            [
                'question_text' => 'Candi Borobudur yang merupakan candi Buddha terbesar di dunia terletak di provinsi...',
                'option_a' => 'Jawa Barat',
                'option_b' => 'Jawa Tengah',
                'option_c' => 'DI Yogyakarta',
                'option_d' => 'Jawa Timur',
                'option_e' => 'Bali',
                'correct_answer' => 'B',
                'points' => 20,
            ],
            [
                'question_text' => 'Danau vulkanik terbesar di Asia Tenggara dan salah satu yang terdalam di dunia adalah...',
                'option_a' => 'Danau Singkarak',
                'option_b' => 'Danau Matano',
                'option_c' => 'Danau Toba',
                'option_d' => 'Danau Poso',
                'option_e' => 'Danau Ranau',
                'correct_answer' => 'C',
                'points' => 20,
            ],
            [
                'question_text' => 'Rumah adat Tongkonan dengan atap melengkung menyerupai perahu berasal dari suku...',
                'option_a' => 'Toraja (Sulawesi Selatan)',
                'option_b' => 'Minangkabau (Sumatera Barat)',
                'option_c' => 'Dayak (Kalimantan)',
                'option_d' => 'Asmat (Papua)',
                'option_e' => 'Sasak (Lombok)',
                'correct_answer' => 'A',
                'points' => 20,
            ],
            [
                'question_text' => 'Berapakah hasil dari 25% dari 240 ditambah 15?',
                'option_a' => '65',
                'option_b' => '70',
                'option_c' => '75',
                'option_d' => '80',
                'option_e' => '85',
                'correct_answer' => 'C',
                'points' => 20,
            ],
            [
                'question_text' => 'Planet dalam tata surya kita yang dijuluki sebagai "Planet Merah" adalah...',
                'option_a' => 'Merkurius',
                'option_b' => 'Venus',
                'option_c' => 'Jupiter',
                'option_d' => 'Mars',
                'option_e' => 'Saturnus',
                'correct_answer' => 'D',
                'points' => 20,
            ],
        ];

        foreach ($questionsData as $idx => $q) {
            Question::create(array_merge($q, [
                'quiz_id' => $quiz->id,
                'order_num' => $idx + 1,
            ]));
        }

        // Demo Participants for Live Monitoring & Result preview
        $participantsData = [
            [
                'name' => 'Aurelia Salsabila',
                'status' => 'completed',
                'score' => 100,
                'total_correct' => 5,
                'total_answered' => 5,
                'total_questions' => 5,
                'started_at' => now()->subMinutes(25),
                'finished_at' => now()->subMinutes(18),
            ],
            [
                'name' => 'Bintang Pratama',
                'status' => 'completed',
                'score' => 80,
                'total_correct' => 4,
                'total_answered' => 5,
                'total_questions' => 5,
                'started_at' => now()->subMinutes(20),
                'finished_at' => now()->subMinutes(12),
            ],
            [
                'name' => 'Clarissa Putri',
                'status' => 'in_progress',
                'score' => 40,
                'total_correct' => 2,
                'total_answered' => 3,
                'total_questions' => 5,
                'started_at' => now()->subMinutes(5),
                'finished_at' => null,
            ],
            [
                'name' => 'Daffa Maulana',
                'status' => 'in_progress',
                'score' => 20,
                'total_correct' => 1,
                'total_answered' => 2,
                'total_questions' => 5,
                'started_at' => now()->subMinutes(3),
                'finished_at' => null,
            ],
        ];

        foreach ($participantsData as $p) {
            Participant::create(array_merge($p, [
                'quiz_id' => $quiz->id,
                'session_token' => Str::random(32),
            ]));
        }
    }
}
