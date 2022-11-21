<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReportTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('report', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('listening_score')->default(0);
            $table->string('reading_score')->default(0);
            $table->string('num_listening')->default(0);
            $table->string('num_reading')->default(0);
            $table->bigInteger('exam_id');
            $table->bigInteger('user_id');
            $table->timestamps();

            $table->foreign('exam_id')->references('id')->on('exam');
            $table->foreign('user_id')->references('id')->on('user');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('report');
    }
}
