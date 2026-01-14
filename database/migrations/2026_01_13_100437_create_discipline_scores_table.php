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
        Schema::create('discipline_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->foreignId('period_id')->constrained()->onDelete('cascade');
            $table->integer('total_work_days')->default(0);
            $table->integer('present_on_time')->default(0)->comment('E - Datang Tepat Waktu');
            $table->integer('leave_on_time')->default(0)->comment('L - Pulang Tepat Waktu');
            $table->integer('late_minutes')->default(0)->comment('G-K - Total menit terlambat');
            $table->integer('early_leave_minutes')->default(0)->comment('N-R - Total menit pulang awal');
            $table->integer('excess_permission_count')->default(0)->comment('S/AC/V/AA/AB/AE/AI/AJ count');
            $table->decimal('score_1', 5, 2)->default(0)->comment('Tingkat Kehadiran & Ketepatan Waktu (50%)');
            $table->decimal('score_2', 5, 2)->default(0)->comment('Kedisiplinan - Tanpa Pelanggaran (35%)');
            $table->decimal('score_3', 5, 2)->default(0)->comment('Ketaatan - Tanpa Izin Berlebih (15%)');
            $table->decimal('final_score', 5, 2)->default(0)->comment('Total Skor (0-100)');
            $table->integer('rank')->nullable();
            $table->boolean('is_winner')->default(false);
            $table->json('raw_data')->nullable()->comment('Original Excel data for audit');
            $table->timestamps();

            $table->unique(['employee_id', 'period_id']);
            $table->index('period_id');
            $table->index('final_score');
            $table->index('rank');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discipline_scores');
    }
};
