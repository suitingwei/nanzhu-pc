<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBannersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		Schema::create('banners', function (Blueprint $table) {
			$table->increments('id');
			$table->string("url");
			$table->string("cover");
			$table->string("type")->default("h5");
			$table->integer("sort")->default(0);
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
