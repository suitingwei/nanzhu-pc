<?php

namespace App\Models;

use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed  share_group
 * @property string read_ids
 */
class Todo extends Model
{
    //
    protected $fillable = ["is_read", "share_ids", "movie_id", "share_group", "title", "content", "user_id", "date"];

    public function toArray()
    {
        $array['id']          = $this->id;
        $array['is_read']     = $this->is_read;
        $array['share_ids']   = $this->share_ids;
        $array['movie_id']    = $this->movie_id;
        $array['share_group'] = $this->share_group;
        $array['title']       = $this->title;
        $array['content']     = $this->content;
        $array['user_id']     = $this->user_id;
        $array['date']        = $this->date;

        //这里强制转换为Carbon对象的原因是:
        //备忘录在某个人读取的时候要把这个人添加到read_ids,但是这个时候不能更新updated_at,那个代表上一次编辑时间
        //所以把timestamps ==> false,但是这样的话,updated_at就变成了string,所以需要强制转换
        $array['last_updated'] = Carbon::createFromTimestamp(strtotime($this->updated_at));
        $array['editor']       = "";

        //用户删除的时候不会删除备忘录(因为可以同步别人查看),所以有可能用户不存在
        $user                = User::where("FID", $this->user_id)->first();
        $array['editor']     = $user ? $user->FNAME : '';
        $movie               = Movie::where("FID", $this->movie_id)->first();
        $array['movie_name'] = $movie ? $movie->FNAME : '';

        $array['group'] = $this->groupNames;
        return $array;
    }

    /**
     * 添加新的已读用户id到read_ids
     *
     * @param $newUserId
     */
    public function addNewReadUserId($newUserId)
    {
        //如果不分享,不用管
        if (empty($newUserId) || $this->notShared()) {
            return;
        }

        $readUserIdArray = explode(',', $this->read_ids);

        //已经读了,不用管
        if (in_array($newUserId, $readUserIdArray)) {
            return;
        }

        array_push($readUserIdArray, $newUserId);

        $this->read_ids   = implode(',', $readUserIdArray);
        $this->timestamps = false;
        $this->save();
    }

    /**
     * 判断这个备忘录是否共享
     * @return bool
     */
    public function notShared()
    {
        return !$this->shared();
    }

    /**
     * 判断这个备忘录是否共享
     * @return bool
     */
    public function shared()
    {
        return $this->share_group == 1;
    }

    /**
     * 用户是否可以看到这个备忘录
     */
    public function scopeCanSee($query, $userId)
    {
        $query->where(function ($query) use ($userId) {
            $query->where('user_id', $userId)
                  ->orWhere('share_ids', 'like', "%{$userId}%");
        });
    }

    /**
     * 用户是否阅读备忘录
     *
     * @param $readerId
     *
     * @return bool
     */
    public function isReadByUser($readerId)
    {
        return ($this->user_id == $readerId) || in_array($readerId, explode(',', $this->read_ids));
    }

    /**
     * 获取备忘录创建者的部门
     */
    public function getGroupNamesAttribute()
    {
        $groupUsers = GroupUser::where(['FMOVIE' => $this->movie_id, 'FUSER' => $this->user_id])->get();
        //需要这个判断,因为发备忘录的人可能已经退出部门
        if ($groupUsers->count() == 0) {
            return '';
        }

        $groupIds = array_unique($groupUsers->lists('FGROUP')->all());

        //需要进行存在判断,因为可能部门已经被删除
        $groups = Group::whereIn('FID', $groupIds)->get();

        if ($groups->count() == 0) {
            return '';
        }

        return implode('/', $groups->lists('FNAME')->all());
    }

    /**
     * 添加新的用户到接收人名单
     *
     * @param $userId
     *
     * @return bool
     */
    public function addUserToReceivers($userId)
    {
        $scopeIdArray = explode(',', $this->share_ids);

        if (in_array($userId, $scopeIdArray)) {
            return true;
        }

        array_push($scopeIdArray, $userId);

        $this->update(['share_ids' => implode(',', $scopeIdArray)]);

        return true;
    }


    /**
     * 共享的备忘录
     *
     * @param $query
     *
     * @return
     */
    public function scopeShare($query)
    {
        return $query->where('share_group', 1);
    }
}
