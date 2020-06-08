<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTestingResultsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('testing_results', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('testing_session_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('answered_questions_number');
            $table->unsignedBigInteger('correct_questions_number');
            $table->unsignedBigInteger('time_elapsed');
            $table->foreign('subject_id')->references('id')->on('subjects')->onCascade('delete');
            $table->foreign('testing_session_id')->references('id')->on('testing_sessions')->onCascade('delete');
            $table->foreign('user_id')->references('id')->on('users')->onCascade('delete');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('testing_results');
    }
}
