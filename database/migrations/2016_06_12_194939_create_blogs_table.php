<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBlogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
		Schema::create('blogs', function (Blueprint $table) {
			$table->increments('id');
			$table->string("author_id");
			$table->string("type");
			$table->text("content");
			$table->string("title");
			$table->integer("is_read")->default(0);
			$table->integer("is_show")->default(0);
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
