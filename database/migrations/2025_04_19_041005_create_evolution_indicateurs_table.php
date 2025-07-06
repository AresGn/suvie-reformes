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
        Schema::create('evolution_indicateurs', function (Blueprint $table) {
            $table->foreignId('reforme_indicateur_id')->constrained('reformes_indicateurs');
            $table->date('date_evolution');
            $table->decimal('valeur', 15, 2);
            $table->primary(['reforme_indicateur_id', 'date_evolution']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evolution_indicateurs');
    }
};
