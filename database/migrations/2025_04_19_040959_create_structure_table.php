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
        Schema::create('structure', function (Blueprint $table) {
            $table->id();
            $table->string('lib_court')->unique();
            $table->string('lib_long');
            $table->foreignId('responsable')->constrained('personne');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('structure');
    }
};
