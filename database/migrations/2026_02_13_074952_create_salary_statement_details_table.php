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
        Schema::create('salary_statement_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('salary_statement_id');
            $table->foreign('salary_statement_id', 'fk_ssd_ss')
                ->references('id')
                ->on('salary_statements')
                ->cascadeOnDelete();

            $table->smallInteger('formulable_type');
            $table->smallInteger('component_type')->nullable();
            $table->string('component_name');

            $table->json('component_values')->nullable();

            $table->decimal('taxable', 21, 6)->default(0);
            $table->decimal('nontaxable', 21, 6)->default(0);
            $table->decimal('deduction', 21, 6)->default(0);
            $table->decimal('contribution', 21, 6)->default(0);
            $table->decimal('withholding_tax', 21, 6)->default(0);
            $table->decimal('net', 21, 6)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_statement_details');
    }
};
