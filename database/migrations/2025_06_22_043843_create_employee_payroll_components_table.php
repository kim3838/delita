<?php

use App\Enums\AmountablePayrollComponentEnd;
use App\Enums\AmountablePayrollComponentStart;
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
            $table->smallInteger('amountable_start')->default(AmountablePayrollComponentStart::NOT_SPECIFIED);
            $table->date('start_date')->nullable();
            $table->smallInteger('amountable_end')->default(AmountablePayrollComponentEnd::NOT_SPECIFIED);
            $table->date('end_date')->nullable();
            $table->timestamps();

            $table->index(['payroll_componentable_type', 'payroll_componentable_id'], 'emp_payroll_comp_morph_idx');
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
