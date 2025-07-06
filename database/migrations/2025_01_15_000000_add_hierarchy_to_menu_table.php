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
        Schema::table('menu', function (Blueprint $table) {
            $table->unsignedBigInteger('parent_id')->nullable()->after('id');
            $table->integer('ordre')->default(0)->after('url');
            $table->boolean('is_active')->default(true)->after('ordre');
            
            $table->foreign('parent_id')->references('id')->on('menu')->onDelete('cascade');
            $table->index(['parent_id', 'ordre']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menu', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['parent_id', 'ordre', 'is_active']);
        });
    }
};
