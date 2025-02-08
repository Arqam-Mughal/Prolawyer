<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHearingCourtsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hearing_courts', function (Blueprint $table) {
            $table->id(); // Primary Key
            $table->string('court_type'); // Type of court (e.g., District, High)
            $table->boolean('state_required')->default(false); // Requires state dropdown
            $table->boolean('district_required')->default(false); // Requires district dropdown
            $table->boolean('bench_required')->default(false); // Requires bench dropdown
            $table->boolean('case_type_required')->default(false); // Requires case type dropdown
            $table->string('case_number_format')->nullable(); // Case number format (e.g., Type/Number/Year)
            $table->string('source')->nullable(); // Source of data (e.g., api_courts, Mercury)
            $table->boolean('is_active')->default(true); // Active status for the court
            $table->timestamps(); // Created at and updated at
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('hearing_courts');
    }
}
