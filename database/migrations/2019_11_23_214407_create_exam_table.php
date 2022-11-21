<?php

use App\Company;
use App\Exam;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateExamTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('exam', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('image_preview')->nullable();
            $table->dateTime('from_date')->nullable();
            $table->dateTime('to_date')->nullable();
            $table->string('status');
            $table->bigInteger('company_id')->default(Company::SYSTEM_COMPANY);
            $table->bigInteger('exam_question_id');
            $table->string('is_shuffle_answer')->default(Exam::NOT_SHUFFLE_ANSWER);
            $table->string('is_allow_view_answer')->default(Exam::DENY_VIEW_ANSWERS);
            $table->timestamps();

            $table->foreign('exam_question_id')->references('id')->on('exam_question');
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
        Schema::dropIfExists('exam');
    }
}
