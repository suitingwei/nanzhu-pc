<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
		Schema::create('messages', function (Blueprint $table) {
			$table->increments('id');
			$table->integer("from");
			$table->string("type");
			$table->string("uri");
			$table->text("content");
			$table->integer("is_delete")->default(0);
			$table->integer("is_undo")->default(0);
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
