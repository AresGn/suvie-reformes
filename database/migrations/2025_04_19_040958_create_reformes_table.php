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
        Schema::create('reformes', function (Blueprint $table) {
            $table->id();
            $table->string('titre');
            $table->text('objectifs');
            $table->decimal('budget', 15, 2)->nullable();
            $table->date('date_debut');
            $table->date('date_fin_prevue');
            $table->date('date_fin')->nullable();
            $table->char('statut', 1)->default('C')->check('statut IN ("C", "P", "A")');
            $table->string('pieces_justificatifs')->nullable();
            $table->foreignId('type_reforme')->constrained('type_reforme');
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
        Schema::dropIfExists('reformes');
    }
};
