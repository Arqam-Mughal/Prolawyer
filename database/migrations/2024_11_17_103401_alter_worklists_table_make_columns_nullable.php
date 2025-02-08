<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterWorklistsTableMakeColumnsNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('worklists', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id')->nullable()->change();
            $table->integer('end_occurrences')->nullable()->change();
            $table->integer('passed_occurrences')->nullable()->change();
            $table->integer('status')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('worklists', function (Blueprint $table) {
            $table->unsignedBigInteger('category_id')->nullable(false)->change();
            $table->integer('end_occurrences')->nullable(false)->change();
            $table->integer('passed_occurrences')->nullable(false)->change();
            $table->integer('status')->nullable(false)->change();
        });
    }
}
