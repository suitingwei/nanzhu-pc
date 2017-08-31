<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		Schema::create('profiles', function (Blueprint $table) {
			$table->increments('id');
			$table->string("avatar");
			$table->string("name");
			$table->string("height");
			$table->string("weight");
			$table->string("constellation");
			$table->string("blood_type");
			$table->string("work_ex");
			$table->string("prize_ex");
			$table->string("mobile");
			$table->integer("user_id");

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
