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
        Schema::create('reformes_structure', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reforme_id')->constrained('reformes');
            $table->foreignId('structure_id')->constrained('structure');
            $table->unique(['reforme_id', 'structure_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reformes_structure');
    }
};
