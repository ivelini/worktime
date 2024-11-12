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
        Schema::create('sheet_time', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('emp_id');
            $table->bigInteger('emp_code');
            $table->string('surname');
            $table->string('name');
            $table->timestamp('date');
            $table->string('schedule_name');
            $table->string('position');
            $table->string('min_time')->nullable();
            $table->string('max_time')->nullable();
            $table->string('work_min_time')->nullable();
            $table->string('work_max_time')->nullable();
            $table->integer('duration')->nullable();
            $table->integer('advance')->nullable();
            $table->integer('salary_amount')->nullable();
            $table->integer('per_pay_hour')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sheet_time');
    }
};
