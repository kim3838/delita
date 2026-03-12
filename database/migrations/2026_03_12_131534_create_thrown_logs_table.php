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
        Schema::create('thrown_logs', function (Blueprint $table) {
            $table->id();
            $table->string('thrown');
            $table->boolean('is_exception')->default(false);
            $table->boolean('is_error')->default(false);
            $table->string('message');
            $table->string('file');
            $table->integer('line');
            $table->string('request');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('thrown_logs');
    }
};
