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
        Schema::create('lesson_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId(column: 'user_id')->constrained()->cascadeOnDelete();
            $table->foreignId(column: 'lesson_id')->constrained()->cascadeOnDelete();
            $table->timestamp('viewed_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedBigInteger('playback_time_seconds')->nullable();
            $table->timestamps();

            $table->unique(columns: ['user_id', 'lesson_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lesson_user');
    }
};
