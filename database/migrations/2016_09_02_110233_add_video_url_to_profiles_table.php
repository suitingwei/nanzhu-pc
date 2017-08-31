<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVideoUrlToProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->string('self_video_url');        //自我介绍视频
            $table->string('collection_video_url');  //个人集锦视频,逗号分隔,最多允许上传两组
            $table->string('before_position');       //幕后身份
            $table->string('behind_position');       //台前身份
            $table->string('schedule');           //档期
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('profiles', function (Blueprint $table) {
            $table->dropColumn('self_video_url');
            $table->dropColumn('collection_video_url');
            $table->dropColumn('before_position');
            $table->dropColumn('behind_position');
            $table->dropColumn('schedule');
        });
    }
}
