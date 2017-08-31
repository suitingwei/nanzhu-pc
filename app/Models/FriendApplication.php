<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class FriendApplication extends Model
{
    const NOT_APPROVED = false;
    const APPROVED     = true;

    public $guarded = [];

    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopeApproved($query)
    {
        return $query->where('is_approved', false);
    }

    public $casts = ['is_approved' => 'boolean'];

    /**
     * @param $query
     *
     * @return mixed
     */
    public function scopeNotApproved($query)
    {
        return $query->where('is_approved', false);
    }

    public static function boot()
    {
        parent::boot();

        static::updated(function ($friendApplication) {
            //如果是同意好友,添加到好友列表
            //好友是双向的,所以方便起见存两条数据,以后查询的时候简单
            if ($friendApplication->is_approved) {
                Friend::create([
                    'user_id'   => $friendApplication->applier_id,
                    'friend_id' => $friendApplication->receiver_id
                ]);

                Friend::create([
                    'user_id'   => $friendApplication->receiver_id,
                    'friend_id' => $friendApplication->applier_id
                ]);

                //添加环信好友因为删除是双线的,所以加好友也是双向的
                User::find($friendApplication->receiver_id)->addHxFriend($friendApplication->applier_id);
                User::find($friendApplication->applier_id)->addHxFriend($friendApplication->receiver_id);
            }
        });
    }
}
