<?php

use App\Company;
use App\ExamQuestion;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExamQuestionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('exam_question', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->longtext('listening_desc')->nullable();
            $table->longtext('reading_desc')->nullable();
            $table->string('audio')->nullable();
            $table->bigInteger('company_id')->default(Company::SYSTEM_COMPANY);
            $table->boolean('for_system')->default(false);
            $table->bigInteger('exam_question_score_id')->nullable();
            $table->string('status')->default(ExamQuestion::UNCOMPLETED);
            $table->timestamps();

            $table->foreign('exam_question_score_id')->references('id')->on('exam_question_score');
            $table->foreign('company_id')->references('id')->on('company');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('exam_question');
    }
}
