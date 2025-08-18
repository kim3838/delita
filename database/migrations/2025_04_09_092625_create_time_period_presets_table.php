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
        Schema::create('time_period_presets', function (Blueprint $table) {
            $table->id();
            $table->smallInteger('type');
            $table->string('name');
            $table->string('readable_name');
            $table->json('yearly_period')->nullable();
            $table->json('monthly_period')->nullable();
            $table->json('semimonthly_period')->nullable();
            $table->json('hour_period')->nullable();

            $table->unique(['type', 'name']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('time_period_presets');
    }
};
