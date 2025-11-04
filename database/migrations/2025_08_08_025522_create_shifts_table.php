<?php

use App\Enums\ShiftHolidayPolicy;
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
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->ulid()->unique()->index();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->string('code');
            $table->string('name');
            $table->smallInteger('type');
            $table->smallInteger('holiday_policy')->default(ShiftHolidayPolicy::DAY_OFF);
            $table->tinyInteger('work_start_grace_time')->default(0);
            $table->boolean('require_lunch_time_in_and_out')->default(false);
            $table->tinyInteger('lunch_start_grace_time')->default(0);
            $table->decimal('max_overtime', 4, 2)->default(0);
            $table->timestamps();

            $table->unique(['company_id', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shifts');
    }
};
