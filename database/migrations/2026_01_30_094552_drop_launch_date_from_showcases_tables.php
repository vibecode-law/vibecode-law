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
        Schema::table('showcases', function (Blueprint $table) {
            $table->dropColumn('launch_date');
        });

        Schema::table('showcase_drafts', function (Blueprint $table) {
            $table->dropColumn('launch_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('showcases', function (Blueprint $table) {
            $table->date('launch_date')->nullable();
        });

        Schema::table('showcase_drafts', function (Blueprint $table) {
            $table->date('launch_date')->nullable();
        });
    }
};
