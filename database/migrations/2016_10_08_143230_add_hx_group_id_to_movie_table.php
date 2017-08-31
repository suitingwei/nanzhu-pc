<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddHxGroupIdToMovieTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('t_biz_movie', function (Blueprint $table) {
            $table->string('hx_group_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('t_biz_movie', function (Blueprint $table) {
            $table->dropColumn('hx_group_id');
        });
    }
}
