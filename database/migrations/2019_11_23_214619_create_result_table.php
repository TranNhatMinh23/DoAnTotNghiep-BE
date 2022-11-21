<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateResultTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('result', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('your_answer')->nullable();
            $table->bigInteger('report_id');
            $table->bigInteger('question_id');
            $table->bigInteger('your_answer_code')->nullable();
            $table->bigInteger('position_1')->nullable();
            $table->bigInteger('position_2')->nullable();
            $table->bigInteger('position_3')->nullable();
            $table->bigInteger('position_4')->nullable();
            $table->timestamps(); 

            $table->foreign('report_id')->references('id')->on('report');
            $table->foreign('question_id')->references('id')->on('question');
            $table->foreign('your_answer_code')->references('id')->on('answer');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('result');
    }
}
