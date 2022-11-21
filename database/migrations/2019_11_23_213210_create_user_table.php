<?php

use App\Company;
use App\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('gender')->nullable();
            $table->string('birthday')->nullable();
            $table->string('avatar_url')->nullable();
            $table->string('email')->unique();
            $table->string('password');
            $table->bigInteger('role_id');
            $table->bigInteger('company_id')->default(Company::SYSTEM_COMPANY);
            $table->string('active_status')->default(User::ACTIVE_USER);
            $table->timestamp('email_verified_at')->nullable();
            $table->string('verified')->default(User::UNVERIFIED_USER);
            $table->string('verification_token')->nullable();
            $table->timestamps();
            $table->softDeletes(); //deleted_at

            $table->foreign('role_id')->references('id')->on('role');
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
        Schema::dropIfExists('user');
    }
}
