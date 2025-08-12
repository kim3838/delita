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
        Schema::create('shift_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shift_id')->constrained('shifts')->onDelete('cascade');
            $table->smallInteger('week_day');
            $table->boolean('is_rest_day');
            $table->boolean('is_day_off');
            $table->string('timezone')->nullable();
            $table->boolean('is_flexible');
            $table->time('work_start')->nullable();
            $table->time('work_end')->nullable();
            $table->string('total_work_hours_with_breaks')->nullable();
            $table->boolean('has_lunch_break');
            $table->time('lunch_break_start')->nullable();
            $table->time('lunch_break_end')->nullable();
            $table->string('total_lunch_break_hours')->nullable();
            $table->boolean('is_lunch_break_compensable')->default(false);
            $table->timestamps();

            $table->unique(['shift_id', 'week_day']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shift_schedules');
    }
};
