<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateJoinGroupTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('join_group', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->integer('movie_id');
            $table->integer('group_id');
            $table->string('audit_status');
            $table->integer('audit_user_id');
            $table->timestamp('audit_at');
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
        Schema::drop('join_group');
    }
}
