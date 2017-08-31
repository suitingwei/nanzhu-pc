<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePushRecordsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		Schema::create('push_records', function (Blueprint $table) {
			$table->increments('id');
			$table->string("aliyuntokens");
			$table->string("title");
			$table->string("body");
			$table->string("summary");
			$table->string("extra");
			$table->string("status");
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
