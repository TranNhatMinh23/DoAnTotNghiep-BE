<?php

use App\Article;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateArticleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('article', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('title');
            $table->string('image_url')->nullable(); 
            $table->longtext('description')->nullable();
            $table->longtext('content')->nullable();
            $table->bigInteger('category_id');
            $table->string('status')->default(Article::ACTIVE);
            $table->timestamps(); 

            $table->foreign('category_id')->references('id')->on('category');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('article');
    }
}
