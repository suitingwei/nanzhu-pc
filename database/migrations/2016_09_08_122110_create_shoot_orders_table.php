<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateShootOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
		Schema::create('shoot_orders', function (Blueprint $table) {
			$table->increments('id');
			$table->string("start_date");
			$table->string("address");
			$table->string("phone");
			$table->string("contact");
			$table->text("note");
			$table->integer("is_payed")->default(0);
			$table->datetime("payed_at");
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
    }
}
