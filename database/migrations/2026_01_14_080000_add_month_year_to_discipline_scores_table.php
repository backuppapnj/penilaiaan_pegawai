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
        Schema::table('discipline_scores', function (Blueprint $table) {
            // Drop foreign keys first to avoid index dependency issues
            $table->dropForeign(['employee_id']);
            $table->dropForeign(['period_id']);
            
            // Drop old unique constraint
            $table->dropUnique(['employee_id', 'period_id']);
            
            // Add new columns
            $table->unsignedTinyInteger('month')->after('period_id')->nullable();
            $table->unsignedSmallInteger('year')->after('month')->nullable();
            
            // Modify period_id to be nullable
            $table->unsignedBigInteger('period_id')->nullable()->change();
            
            // Add new unique constraint
            $table->unique(['employee_id', 'month', 'year']);
            
            // Re-add foreign keys
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('period_id')->references('id')->on('periods')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('discipline_scores', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
            $table->dropForeign(['period_id']);
            $table->dropUnique(['employee_id', 'month', 'year']);
            
            $table->dropColumn(['month', 'year']);
            
            // This might fail if there are nulls, but we assume down is only used if we want to revert empty/valid state
            $table->unsignedBigInteger('period_id')->nullable(false)->change();
            
            $table->unique(['employee_id', 'period_id']);
            
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('period_id')->references('id')->on('periods')->onDelete('cascade');
        });
    }
};