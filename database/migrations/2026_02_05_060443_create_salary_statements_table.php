<?php

use App\Enums\SalaryStatementType;
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
        Schema::create('salary_statements', function (Blueprint $table) {
            $table->id();
            $table->ulid()->unique()->index();
            $table->foreignId('payroll_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();

            $table->smallInteger('type')->default(SalaryStatementType::DEFAULT);
            $table->boolean('is_paid')->default(false);

            $table->tinyInteger('total_days')->default(0);
            $table->tinyInteger('total_day_offs')->default(0);
            $table->tinyInteger('total_working_days')->default(0);
            $table->tinyInteger('total_regular_work_days')->default(0);
            $table->tinyInteger('total_working_rest_days')->default(0);
            $table->tinyInteger('total_special_holidays')->default(0);
            $table->tinyInteger('total_legal_holidays')->default(0);
            $table->tinyInteger('total_double_holidays')->default(0);
            $table->tinyInteger('total_full_present')->default(0);
            $table->tinyInteger('total_present_with_irregularity')->default(0);
            $table->tinyInteger('total_leave_without_pay')->default(0);
            $table->tinyInteger('total_leave_with_pay')->default(0);
            $table->tinyInteger('total_absent')->default(0);

            $table->decimal('taxable', 21, 6)->default(0);
            $table->decimal('nontaxable', 21, 6)->default(0);
            $table->decimal('contribution', 21, 6)->default(0);
            $table->decimal('withholding_tax', 21, 6)->default(0);
            $table->decimal('deduction', 21, 6)->default(0);
            $table->decimal('net', 21, 6)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_statements');
    }
};
