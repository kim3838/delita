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
        Schema::create('salary_statement_attendance_payroll_components', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('salary_statement_attendance_id');
            $table->foreign('salary_statement_attendance_id', 'fk_ssapc_ssa')
                ->references('id')
                ->on('salary_statement_attendances')
                ->cascadeOnDelete();

            $table->smallInteger('formulable_type');
            $table->smallInteger('component_type');
            $table->string('component_sub_type');
            $table->string('component_name');
            $table->decimal('regular_pay', 21, 6)->default(0);
            $table->decimal('night_differential_pay', 21, 6)->default(0);
            $table->decimal('rest_day_pay', 21, 6)->default(0);
            $table->decimal('total', 21, 6)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_statement_attendance_payroll_components');
    }
};
