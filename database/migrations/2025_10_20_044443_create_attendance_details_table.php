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
        Schema::create('attendance_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->smallInteger('split_type');
            $table->string('split_start', 5);
            $table->string('split_end', 5);
            $table->smallInteger('split_duration');
            $table->smallInteger('work_hour_type');
            $table->smallInteger('hourly_rate_type');
            $table->decimal('regular_rate_multiplier', 8, 6)->nullable();
            $table->decimal('non_rest_rate_multiplier', 8, 6)->nullable();
            $table->decimal('hourly_rate_multiplier', 8, 6);
            $table->decimal('base_rate_multiplier', 8, 6);
            $table->smallInteger('order');
            $table->datetime('actual_start')->nullable();
            $table->datetime('actual_end')->nullable();
            $table->string('grace_before_start_applied', 5)->nullable();
            $table->string('grace_after_start_applied', 5)->nullable();
            $table->boolean('first_in')->default(false);
            $table->boolean('lunch_out')->default(false);
            $table->boolean('lunch_in')->default(false);
            $table->boolean('last_out')->default(false);
            $table->boolean('overtime_start')->default(false);
            $table->boolean('overtime_end')->default(false);
            $table->datetime('actual_present_start')->nullable();
            $table->datetime('actual_present_end')->nullable();
            $table->smallInteger('actual_present')->nullable();
            $table->datetime('actual_irregularity_duration_start')->nullable();
            $table->datetime('actual_irregularity_duration_end')->nullable();
            $table->smallInteger('actual_irregularity_duration')->nullable();
            $table->smallInteger('late')->default(0);
            $table->smallInteger('undertime')->default(0);
            $table->smallInteger('flexible_undertime')->default(0);

            $table->decimal('regular_pay', 21, 6)->default(0);
            $table->decimal('night_differential_pay', 21, 6)->default(0);
            $table->decimal('rest_day_pay', 21, 6)->default(0);
            $table->decimal('holiday_pay', 21, 6)->default(0);

            $table->timestamps();

            $table->unique(['attendance_id', 'order']);

            $table->index(['attendance_id', 'split_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_details');
    }
};
