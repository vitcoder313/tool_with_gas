<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('create_users_table', function (Blueprint $table) {
            $table->id();
            $table->string("name")->comment("name");
$table->string("email", 255)->unique()->comment("email");
$table->string("password", 255)->comment("password");

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('create_users_table');
    }
};
