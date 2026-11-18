<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->unsignedTinyInteger('options_count')->default(5)->after('duration_minutes'); // jumlah pilihan ganda per kuis (2-20)
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->json('options_data')->nullable()->after('option_e'); // menyimpan opsi dinamis A s.d T jika opsi > 5 atau format terstruktur
            $table->string('correct_answer', 10)->change(); // antisipasi kunci jawaban dua digit atau lebih (misal J, K, ..., T)
        });

        Schema::table('participant_answers', function (Blueprint $table) {
            $table->string('selected_option', 10)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropColumn('options_count');
        });

        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn('options_data');
            $table->string('correct_answer', 5)->change();
        });

        Schema::table('participant_answers', function (Blueprint $table) {
            $table->string('selected_option', 5)->nullable()->change();
        });
    }
};
