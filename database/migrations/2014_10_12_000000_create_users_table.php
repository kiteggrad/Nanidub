<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';

            $table->tinyIncrements('id');
            $table->string(         'name', 50)->unique();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->charset = 'utf8';
            $table->collation = 'utf8_general_ci';

            $table->increments(         'id');
            $table->string(             'login', 50)->unique();
            $table->string(             'email', 50)->unique();
            $table->unsignedTinyInteger('role_id')  ->default(1);
            $table->string(             'password');
            $table->rememberToken();
            $table->string(             'confirm_token', 100)->nullable();
            $table->timestamps();


            $table->foreign('role_id')->references('id')->on('roles');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('roles');
    }
}
