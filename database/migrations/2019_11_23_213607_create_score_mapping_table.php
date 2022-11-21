<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateScoreMappingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('score_mapping', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('num_of_question')->nullable();
            $table->string('listening_score')->nullable();
            $table->string('reading_score')->nullable();
            $table->bigInteger('exam_question_score_id');
            $table->timestamps();

            $table->foreign('exam_question_score_id')->references('id')->on('exam_question_score');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('score_mapping');
    }
}
