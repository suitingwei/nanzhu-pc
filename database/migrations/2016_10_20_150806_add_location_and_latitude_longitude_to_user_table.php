<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLocationAndLatitudeLongitudeToUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('t_sys_user', function (Blueprint $table) {
            $table->string('location_code');  //天气的编码
            $table->string('longitude');      //精度
            $table->string('latitude');       //未读
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('t_sys_user', function (Blueprint $table) {
            $table->dropColumn('location_code');  //天气的编码
            $table->dropColumn('longitude');      //精度
            $table->dropColumn('latitude');       //未读
        });
    }
}
