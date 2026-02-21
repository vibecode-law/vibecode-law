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
        Schema::create('instructor_lesson', function (Blueprint $table) {
            $table->foreignId(column: 'user_id')->constrained()->cascadeOnDelete();
            $table->foreignId(column: 'lesson_id')->constrained()->cascadeOnDelete();

            $table->primary(columns: ['user_id', 'lesson_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('instructor_lesson');
    }
};
