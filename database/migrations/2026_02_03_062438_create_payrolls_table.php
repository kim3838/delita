<?php

use App\Enums\PayrollStatus;
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
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->ulid()->unique()->index();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('number');
            $table->smallInteger('year');
            $table->tinyInteger('month');
            $table->smallInteger('pay_frequency');
            $table->smallInteger('frequency_sequence')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->smallInteger('status')->default(PayrollStatus::DRAFT);
            $table->string('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
