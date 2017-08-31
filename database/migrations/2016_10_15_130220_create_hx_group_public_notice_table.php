<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateHxGroupPublicNoticeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hx_group_public_notices', function (Blueprint $table) {
            $table->increments('id');
            $table->string('group_id');
            $table->string('hx_group_id');
            $table->integer('editor_id');
            $table->string('content');
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
        Schema::drop('hx_group_public_notices');
    }
}
