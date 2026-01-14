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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('nip', 50)->unique();
            $table->string('nama');
            $table->string('jabatan');
            $table->string('unit_kerja');
            $table->string('golongan', 10);
            $table->date('tmt');
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamps();

            $table->index('nip');
            $table->index('category_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
