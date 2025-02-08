<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApiKeysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('api_keys', function (Blueprint $table) {
            $table->id();
            $table->string('case_subject')->nullable();
            $table->string('display_board_subject')->nullable();
            $table->string('stripe_publishable_key')->nullable();
            $table->string('stripe_secret_key')->nullable();
            $table->string('ccavenue')->nullable();
            $table->string('ccavenue_access_code')->nullable();
            $table->string('ccavenue_key')->nullable();
            $table->string('ccavenue_merchant_id')->nullable();
            $table->string('encryption_key')->nullable();
            $table->timestamps(); // This will automatically create both `created_at` and `updated_at` columns
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('api_keys');
    }
}
