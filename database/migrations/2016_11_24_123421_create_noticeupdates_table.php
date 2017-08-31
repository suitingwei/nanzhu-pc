<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNoticeupdatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('notice_records',function(Blueprint $table) {
            $table->increments('id');
            $table->string('movie_id');
            $table->string('user_id');
            $table->string('team_id');
            $table->string('notice_id');
            $table->string('notice_file_id');
            $table->string('original_file_name');
            $table->string('new_file_name');
            $table->string('editor');
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
        Schema::drop('notice_records');
    }
}
