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
        Schema::create('leave_types', function (Blueprint $table) {
            $table->id();
            $table->ulid()->unique()->index();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->string('code');
            $table->string('name');
            $table->smallInteger('type');
            $table->boolean('is_paid')->default(false);
            $table->boolean('monetizable')->default(false);

            //Limit usage
            $table->boolean('limit_usage')->default(false);
            $table->smallInteger('limit_usage_span_type')->nullable();
            $table->integer('limit_usage_span_value')->default(0);
            $table->integer('limit_usage_value')->default(0);

            //Eligibility
            $table->json('eligibility_employment_types')->nullable();

            //Eligibility balance
            $table->integer('initial_balance_upon_eligibility')->default(0);

            //Period type
            $table->smallInteger('period_type')->nullable();
            //Interval period type
            $table->smallInteger('period_interval_span_type')->nullable();
            $table->integer('period_interval_span_value')->default(0);
            //Calendar year period type
            $table->integer('period_calendar_span_value')->default(1);

            //Carry over balance per new period
            $table->boolean('carry_over_balance_per_new_period')->default(false);
            $table->smallInteger('carry_over_balance_type')->nullable();
            $table->decimal('carry_over_balance_value', 9, 2)->nullable();

            $table->timestamps();

            $table->unique(['company_id', 'code']);

            $table->index(['company_id']);
            $table->index(['company_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_types');
    }
};
