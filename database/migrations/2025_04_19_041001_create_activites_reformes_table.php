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
        Schema::create('activites_reformes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reforme_id')->nullable()->constrained('reformes');
            $table->string('libelle');
            $table->date('date_debut');
            $table->date('date_fin_prevue');
            $table->date('date_fin')->nullable();
            $table->integer('poids');
            $table->char('statut', 1)->default('C')->check('statut IN ("C", "P", "A")');
            $table->foreignId('parent')->nullable()->constrained('activites_reformes');
            $table->foreignId('structure_responsable')->constrained('reformes_structure');
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
        Schema::dropIfExists('activites_reformes');
    }
};
