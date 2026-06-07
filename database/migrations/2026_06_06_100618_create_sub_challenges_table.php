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
        Schema::create('sub_challenges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('challenge_id')->constrained()->cascadeOnDelete();
            $table->string('name', 60);
            $table->string('tagline');
            $table->text('description')->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();
        });

        Schema::table('challenge_showcase', function (Blueprint $table) {
            $table->foreignId('sub_challenge_id')->nullable()->after('showcase_id')->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('challenge_showcase', function (Blueprint $table) {
            $table->dropConstrainedForeignId('sub_challenge_id');
        });

        Schema::dropIfExists('sub_challenges');
    }
};
