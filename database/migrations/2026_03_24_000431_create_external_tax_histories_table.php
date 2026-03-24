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
        Schema::create('external_tax_histories', function (Blueprint $table) {
            $table->id();
            $table->ulid()->unique()->index();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();

            $table->year('year')->unique();
            $table->decimal('total_taxable', 21, 6)->default(0);
            $table->decimal('total_taxable_from_bonus', 21, 6)->default(0);
            $table->decimal('total_nontaxable_bonus', 21, 6)->default(0);
            $table->decimal('total_tax_withheld', 21, 6)->default(0);

            $table->string('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('external_tax_histories');
    }
};
