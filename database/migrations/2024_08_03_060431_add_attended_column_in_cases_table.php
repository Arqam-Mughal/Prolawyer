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
        Schema::table('cases', function (Blueprint $table) {
            $table->boolean('attended')->default(false)->after('court_bench');
            $table->foreignId('assigned_to')->nullable()->after('attended');
            $table->boolean('fav_mark')->default(false)->after('assigned_to');



        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            $table->dropColumn('attended');
        });
    }
};
