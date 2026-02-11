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
        Schema::create('salary_statement_attendances', function (Blueprint $table) {
            $table->id();
            $table->ulid()->unique()->index();
            $table->foreignId('salary_statement_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attendance_id')->nullable()->constrained()->nullOnDelete();
            $table->date('date');
            $table->smallInteger('status');
            $table->smallInteger('day_type');
            $table->timestamps();

            $table->index(['salary_statement_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_statement_attendances');
    }
};
