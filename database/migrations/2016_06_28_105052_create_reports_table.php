<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReportsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		Schema::create('reports', function (Blueprint $table) {
			$table->increments('id');
			$table->string("contact");
			$table->string("title");
			$table->text("content");
			$table->integer("is_delete")->default(0);
			$table->integer("recruit_id")->default(0);
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
