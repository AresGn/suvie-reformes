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
        Schema::create('suivi_activites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activite_reforme_id')->constrained('activites_reformes');
            $table->date('suivi_date');
            $table->text('actions_fait');
            $table->text('actions_a_fait');
            $table->text('difficultes')->nullable();
            $table->text('solutions')->nullable();
            $table->text('observations')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suivi_activites');
    }
};
