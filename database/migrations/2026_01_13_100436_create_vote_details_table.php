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
        Schema::create('vote_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vote_id')->constrained()->onDelete('cascade');
            $table->foreignId('criterion_id')->constrained()->onDelete('cascade');
            $table->decimal('score', 5, 2);
            $table->timestamps();

            $table->unique(['vote_id', 'criterion_id'], 'unique_vote_detail');
            $table->index('vote_id');
            $table->index('criterion_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vote_details');
    }
};
