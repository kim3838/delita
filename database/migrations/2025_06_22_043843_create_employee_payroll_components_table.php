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
        Schema::create('employee_payroll_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->string('payroll_componentable_type');
            $table->unsignedBigInteger('payroll_componentable_id');
            $table->smallInteger('formulable_type')->nullable();
            $table->decimal('amount', 24, 6)->nullable();
            $table->string('currency')->nullable();
            $table->smallInteger('pay_period')->nullable();
            $table->smallInteger('pay_type')->nullable();
            $table->smallInteger('pay_frequency')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_payroll_components');
    }
};
