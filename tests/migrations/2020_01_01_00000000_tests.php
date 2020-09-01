<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Tests extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('test_models', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('first_name');
            $table->string('email');

            $table->unique(['email']);
        });


        Schema::create('test_soft_delete_models', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('first_name');
            $table->string('email');

            $table->unique(['email']);
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('test_soft_delete_models');
    }
}
