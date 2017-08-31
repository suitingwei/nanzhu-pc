<?php

namespace App\Traits\User;

use App\User;
use stdClass;

trait FormatterOperationTrait
{
    private $obj = null;

    /**
     * 转换成对象
     * 用于前端接口同一数据结构
     */
    public function formatBasicClass()
    {
        $this->obj            = new stdClass();
        $this->obj->user_id   = $this->FID;
        $this->obj->user_name = $this->hx_name;
        $this->obj->phone     = $this->FPHONE;
        $this->obj->cover_url = $this->cover_url;

        return $this;
    }

    /**
     * 添加上是否好友的信息
     *
     * @param User|integer $friend
     *
     * @return $this
     */
    public function withFriendInfo($friend)
    {
        if (!($friend instanceof User)) {
            $friend = User::find($friend);
        }

        $this->obj->is_friend = $this->isFriendOfUser($friend);

        return $this;
    }

    /**
     * 获取格式化之后的数据对象
     * @return \stdClass
     */
    public function get()
    {
        return $this->obj;
    }

    /**
     * 当前要用户是否在另一个人的黑名单里
     *
     * @param $ownerUserId
     *
     * @return $this
     */
    public function withBlacklistInfo($ownerUserId)
    {
        $ownerUser = User::find($ownerUserId);

        foreach ($ownerUser->hxBlackLists() as $blackListUser) {
            if ($blackListUser->user_id == $this->FID) {
                $this->obj->is_in_blacklist = true;
                return $this;
            }
        }

        $this->obj->is_in_blacklist = false;
        return $this;
    }

    /**
     * 添加用户在剧组的职位信息
     *
     * @param $movieId
     *
     * @return $this
     */
    public function withPositionInMovie($movieId)
    {
        $this->obj->position = $this->positionInMovie($movieId);
        return $this;
    }

    /**
     * 添加用户在部门名称信息
     * @param $movieId
     *
     * @return $this
     */
    public function withGroupNamesInMovie($movieId)
    {
        $this->obj->groups = $this->groupNamesInMovie($movieId);
        return $this;
    }

    /**
     * 添加用户自己的注册信息
     */
    public function withRegisterInfo()
    {
        $this->obj->is_registered = true;
        return $this;
    }


}


