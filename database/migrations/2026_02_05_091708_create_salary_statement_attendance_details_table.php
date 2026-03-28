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
        Schema::create('salary_statement_attendance_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('salary_statement_attendance_id');
            $table->foreign('salary_statement_attendance_id', 'fk_ssad_ssa')
                ->references('id')
                ->on('salary_statement_attendances')
                ->cascadeOnDelete();

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

            $table->decimal('hourly_rate', 21, 6)->default(0);
            $table->decimal('regular_pay', 21, 6)->default(0);
            $table->decimal('allowance', 21, 6)->default(0);
            $table->decimal('night_differential_pay', 21, 6)->default(0);
            $table->decimal('rest_day_pay', 21, 6)->default(0);
            $table->decimal('leave_pay', 21, 6)->default(0);

            //Might be needed in the future if an employee has fixed pay, date is non-attendance and is a legal holiday
            $table->decimal('holiday_pay', 21, 6)->default(0);
            $table->boolean('holiday_pay_forfeited')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_statement_attendance_details');
    }
};
