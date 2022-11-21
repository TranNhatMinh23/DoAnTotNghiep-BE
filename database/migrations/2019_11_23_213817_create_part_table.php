<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePartTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('part', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('part_no');
            $table->bigInteger('amount');
            $table->longtext('directions')->nullable();
            $table->longtext('ex_question_text')->nullable();
            $table->string('ex_question_image')->nullable();
            $table->text('ex_answera')->nullable();
            $table->text('ex_answerb')->nullable();
            $table->text('ex_answerc')->nullable();
            $table->text('ex_answerd')->nullable();
            $table->string('ex_answer_key')->nullable();
            $table->bigInteger('exam_question_id');
            $table->timestamps();

            $table->foreign('exam_question_id')->references('id')->on('exam_question');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('part');
    }
}
