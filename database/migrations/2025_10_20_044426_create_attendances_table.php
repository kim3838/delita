<?php

use App\Enums\AttendanceStatus;
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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->ulid()->unique()->index();
            $table->foreignId('employee_id')->constrained('employees')->onDelete('cascade');
            $table->foreignId('shift_id')->nullable()->constrained('shifts')->onDelete('set null');
            $table->date('date');
            $table->dateTime('first_in');
            $table->dateTime('lunch_out')->nullable();
            $table->dateTime('lunch_in')->nullable();
            $table->dateTime('last_out');
            $table->smallInteger('status')->default(AttendanceStatus::NOT_SPECIFIED);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
