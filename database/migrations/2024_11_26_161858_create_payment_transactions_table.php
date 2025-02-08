<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id(); // Primary key
            $table->string('transaction_id')->unique(); // Unique transaction ID
            $table->string('gateway'); // Payment gateway name
            $table->decimal('amount', 10, 2); // Transaction amount
            $table->unsignedBigInteger('user_id'); // Reference to users table
            $table->string('currency', 3); // Currency code (e.g., INR)
            $table->string('status'); // Status of the transaction
            $table->json('response')->nullable(); // Response from the payment gateway
            $table->timestamps(); // Created at and updated at timestamps

            // Add foreign key constraint
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_transactions');
    }
}
