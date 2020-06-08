<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('user_id');// in case of regular registered user create this quiestion
            $table->string('name');
            $table->boolean('active');
            $table->boolean('approved');// in case of regular registered user create this quiestion
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onCascade('delete');
            $table->foreign('subject_id')->references('id')->on('subjects')->onCascade('delete');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('questions');
    }
}
