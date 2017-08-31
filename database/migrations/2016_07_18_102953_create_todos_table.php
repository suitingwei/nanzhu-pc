<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTodosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		Schema::create('todos', function (Blueprint $table) {
			$table->increments('id');
			$table->integer("user_id");
			$table->string("date");
			$table->string("title");
			$table->text("content");
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
        //
    }
}
/**
 *  
 */
