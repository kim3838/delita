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
        Schema::create('approval_setting_approvers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approval_setting_id')->constrained('approval_settings')->onDelete('cascade');
            $table->smallInteger('order');
            $table->foreignId('approver_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_setting_approvers');
    }
};
