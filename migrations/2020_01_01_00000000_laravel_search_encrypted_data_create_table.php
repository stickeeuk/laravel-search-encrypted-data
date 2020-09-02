<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class LaravelSearchEncryptedDataCreateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('searchables', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('searchable_type');
            $table->bigInteger('searchable_id')->unsigned();
            $table->string('filter_name');
            $table->string('filter_value');

            $table->unique(['searchable_type', 'searchable_id', 'filter_name']);
            $table->index(['searchable_type', 'filter_name', 'filter_value']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('searchables');
    }
}
