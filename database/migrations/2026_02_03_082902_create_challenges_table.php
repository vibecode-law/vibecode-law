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
        Schema::create('challenges', function (Blueprint $table) {
            $table->id();
            $table->string('title', 80);
            $table->string('slug')->unique();
            $table->string('tagline');
            $table->text('description');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->boolean('is_active')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->foreignId('organisation_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('thumbnail_extension')->nullable();
            $table->json('thumbnail_crops')->nullable();
            $table->timestamps();
        });

        Schema::create('challenge_showcase', function (Blueprint $table) {
            $table->foreignId(column: 'challenge_id')->constrained()->cascadeOnDelete();
            $table->foreignId(column: 'showcase_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->primary(columns: ['challenge_id', 'showcase_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('challenge_showcase');
        Schema::dropIfExists('challenges');
    }
};
