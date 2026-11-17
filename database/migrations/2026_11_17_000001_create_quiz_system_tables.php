<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('code', 20)->unique();
            $table->text('description')->nullable();
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->integer('duration_minutes')->default(30); // durasi kuis dalam menit
            $table->boolean('shuffle_questions')->default(false); // opsi random urutan soal
            $table->boolean('shuffle_options')->default(false); // opsi random pilihan jawaban
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained('quizzes')->cascadeOnDelete();
            $table->integer('order_num')->default(1);
            $table->text('question_text');
            $table->string('option_a')->nullable();
            $table->string('option_b')->nullable();
            $table->string('option_c')->nullable();
            $table->string('option_d')->nullable();
            $table->string('option_e')->nullable();
            $table->string('correct_answer', 5); // 'A', 'B', 'C', 'D', 'E'
            $table->integer('points')->default(10);
            $table->timestamps();
        });

        Schema::create('participants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained('quizzes')->cascadeOnDelete();
            $table->string('session_token', 64)->unique();
            $table->string('name');
            $table->string('identifier')->nullable(); // misal email/no hp/instansi jika ada
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->enum('status', ['in_progress', 'completed', 'abandoned'])->default('in_progress');
            $table->decimal('score', 8, 2)->default(0);
            $table->integer('total_correct')->default(0);
            $table->integer('total_answered')->default(0);
            $table->integer('total_questions')->default(0);
            $table->json('question_order')->nullable(); // order of question IDs for this participant
            $table->json('option_orders')->nullable(); // map of question_id => ['A' => 'B', ...]
            $table->timestamps();
        });

        Schema::create('participant_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('participant_id')->constrained('participants')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('questions')->cascadeOnDelete();
            $table->string('selected_option', 5)->nullable(); // A, B, C, D, E (actual key chosen)
            $table->boolean('is_correct')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('participant_answers');
        Schema::dropIfExists('participants');
        Schema::dropIfExists('questions');
        Schema::dropIfExists('quizzes');
    }
};
