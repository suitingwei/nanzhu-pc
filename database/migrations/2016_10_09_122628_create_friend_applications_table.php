<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFriendApplicationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('friend_applications', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('applier_id'); //申请加好友的人
            $table->string('content');     //加好友信息
            $table->integer('receiver_id'); //申请加的好友
            $table->boolean('is_approved'); //申请是否被通过
            $table->timestamp('approved_at'); //被通过的时间
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
        Schema::drop('friend_applications');
    }
}
