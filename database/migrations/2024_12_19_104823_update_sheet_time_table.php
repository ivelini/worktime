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
        Schema::table('sheet_time', function (Blueprint $table) {
            $table->string('department')->nullable();
            $table->integer('salary_supplement')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sheet_time', function (Blueprint $table) {
            $table->dropColumn('department');
            $table->dropColumn('salary_supplement');
        });
    }
};
