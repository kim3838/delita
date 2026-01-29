<?php

use App\Enums\CreationType;
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
            $table->ulid()->unique()->index();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('company_id')->constrained('companies')->onDelete('cascade');
            $table->foreignId('designation_id')->nullable()->constrained('designations')->nullOnDelete();
            $table->foreignId('manager_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignId('pay_frequency_id')->nullable()->constrained('pay_frequencies')->nullOnDelete();
            $table->string('number');
            $table->string('given_name');
            $table->string('middle_name')->nullable();
            $table->string('family_name');
            $table->date('birth_date')->nullable();
            $table->smallInteger('gender')->default(Gender::NOT_SPECIFIED);
            $table->smallInteger('marital_status')->default(MaritalStatus::NOT_SPECIFIED);
            $table->date('date_registered')->useCurrent();
            $table->smallInteger('creation_type')->default(CreationType::DEFAULT);
            $table->timestamps();

            $table->unique(['user_id', 'company_id']);
            $table->unique(['company_id', 'number']);
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
