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
        Schema::create('attendance_shift_details', function (Blueprint $table){
            $table->id();
            $table->foreignId('attendance_id')->constrained('attendances')->onDelete('cascade');

            /**
             * Shift Assignment
             **/
            $table->date('start_date');
            $table->boolean('stated_shift_end_date')->default(false);
            $table->date('end_date')->nullable();

            /**
             * Shift
             **/
            $table->string('code');
            $table->string('name');
            $table->smallInteger('type');
            $table->smallInteger('holiday_policy');
            $table->json('except_holidays')->nullable();
            $table->tinyInteger('work_start_grace_time')->default(0);
            $table->boolean('require_lunch_time_in_and_out')->default(false);
            $table->tinyInteger('lunch_start_grace_time')->default(0);
            $table->decimal('max_overtime', 4, 2)->default(0);

            /**
             * Shift Schedule
             **/
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
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_shift_details');
    }
};
