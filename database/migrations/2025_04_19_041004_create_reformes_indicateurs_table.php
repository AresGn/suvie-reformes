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
        Schema::create('reformes_indicateurs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reforme_id')->constrained('reformes');
            $table->foreignId('indicateur_id')->constrained('indicateurs');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reformes_indicateurs');
    }
};
