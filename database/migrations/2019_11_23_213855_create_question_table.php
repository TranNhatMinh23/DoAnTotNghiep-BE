<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateQuestionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('question', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->bigInteger('question_no');
            $table->longtext('question_text')->nullable();
            $table->string('question_image')->nullable(); 
            $table->longtext('group_desc')->nullable();
            $table->string('paragraph_image1')->nullable();
            $table->string('paragraph_image2')->nullable();
            $table->string('paragraph_image3')->nullable();
            $table->longtext('paragraph_text1')->nullable();
            $table->longtext('paragraph_text2')->nullable();
            $table->longtext('paragraph_text3')->nullable();
            $table->bigInteger('part_id');
            $table->string('audio')->nullable();
            $table->timestamps();

            $table->foreign('part_id')->references('id')->on('part');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('question');
    }
}
