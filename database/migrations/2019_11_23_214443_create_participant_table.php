<?php

use App\Participant;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateParticipantTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('participant', function (Blueprint $table) {
            $table->bigIncrements('id'); 
            $table->string('email');
            $table->string('regrex')->default(Participant::IS_REGREX);
            $table->bigInteger('exam_id');
            $table->timestamps();

            $table->foreign('exam_id')->references('id')->on('exam');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('participant');
    }
}
