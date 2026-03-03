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
        Schema::table('showcase_images', function (Blueprint $table) {
            $table->json('crops')->nullable()->after('alt_text');
        });

        Schema::table('showcase_draft_images', function (Blueprint $table) {
            $table->json('crops')->nullable()->after('alt_text');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('showcase_images', function (Blueprint $table) {
            $table->dropColumn('crops');
        });

        Schema::table('showcase_draft_images', function (Blueprint $table) {
            $table->dropColumn('crops');
        });
    }
};
