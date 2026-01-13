<?php

use App\Enums\RequestApprovalStatus;
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
        Schema::create('request_approval_states', function (Blueprint $table) {
            $table->id();
            $table->string('requestable_type');
            $table->unsignedBigInteger('requestable_id');
            $table->smallInteger('order');
            $table->foreignId('approver_id')->constrained('users')->onDelete('cascade');
            $table->smallInteger('status')->default(RequestApprovalStatus::PENDING);
            $table->timestamps();

            $table->index(['requestable_type', 'requestable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('request_approval_states');
    }
};
