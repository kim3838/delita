<?php

use App\Enums\Gender;
use App\Enums\MaritalStatus;
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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->string('given_name');
            $table->string('middle_name')->nullable();
            $table->string('family_name');
            $table->date('birth_date');
            $table->smallInteger('gender')->default(Gender::NOT_SPECIFIED);
            $table->smallInteger('marital_status')->default(MaritalStatus::NOT_SPECIFIED);
            $table->string('number')->unique();
            $table->date('date_registered')->default(now());
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
