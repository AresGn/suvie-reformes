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
        Schema::create('user_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('session_id')->unique();
            $table->ipAddress('ip_address');
            $table->text('user_agent');
            $table->timestamp('login_at');
            $table->timestamp('logout_at')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamp('last_activity')->nullable();
            $table->timestamps();

            // Index pour optimiser les performances
            $table->index(['user_id', 'status']);
            $table->index(['session_id']);
            $table->index(['login_at']);
            $table->index(['status']);

            // Clé étrangère vers la table users
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_sessions');
    }
};
