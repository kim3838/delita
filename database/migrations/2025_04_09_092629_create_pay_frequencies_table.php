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
        Schema::create('pay_frequencies', function (Blueprint $table) {
            $table->id();
            $table->ulid('ulid')->unique()->index();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->string('code');
            $table->smallInteger('order');
            $table->smallInteger('type');
            $table->foreignId('time_period_preset_id')->nullable()->constrained('time_period_presets', 'id')->onDelete('set null');
            $table->json('period')->nullable();
            $table->smallInteger('cutoff_type')->nullable();
            $table->smallInteger('cut_off_value')->nullable();
            $table->smallInteger('days_span')->nullable();

            $table->unique(['company_id', 'code']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pay_frequencies');
    }
};
