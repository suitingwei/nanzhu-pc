<?php
namespace App\Traits\User;

use App\Models\EaseUser;
use App\Models\Group;
use App\Models\Movie;

trait HxOperationTrait
{
    /**
     * 加入剧组的群聊
     *
     * @param $movieId
     *
     * @return bool|mixed
     */
    public function joinMovieHxGroup($movieId)
    {
        $movie = Movie::find($movieId);

        if (!$movie) {
            return false;
        }

        return (new EaseUser())->groupAddUser($movie->hx_group_id, $this->FID);
    }

    /**
     * 用户所在的所有群组
     */
    public function joinedHxGroups()
    {
        $chatGroupsInfo = (new EaseUser())->userJoinedGroups($this->FID);

        return $chatGroupsInfo['data'];
    }

    /**
     * 用户是否注册环信
     */
    public function hadRegisterHx()
    {
        return !is_null((new EaseUser())->getUserInfo($this->FID));
    }

    /**
     * 用户没有注册环信
     */
    public function hadNotRegisterHx()
    {
        return !$this->hadRegisterHx();
    }

    /**
     * 用户是否群组的群主
     *
     * @param $hxGroupId
     *
     * @return bool
     *
     */
    public function isOwnerOfHxGroup($hxGroupId)
    {
        $groupOwner = (new EaseUser())->getGroupOwner($hxGroupId);

        return $groupOwner == (EaseUser::USER_NAME_PREFIX . $this->FID);
    }

    /**
     * 添加环信好友
     *
     * @param $friendId
     *
     * @return bool
     */
    public function addHxFriend($friendId)
    {
        try {
            $easeUser = (new EaseUser());

            $easeUser->userAddFriend($this->FID, $friendId);

            //删除好友的时候要把环信用户加入黑名单,不能聊天,所以加为好友的时候要移除黑名单
            $easeUser->userUnBlockUser($this->FID, $friendId);

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * 删除环信好友
     *
     * @param $friendId
     *
     * @return bool
     */
    public function deleteHxFriend($friendId)
    {
        try {
            $easeUser = new EaseUser();
            $easeUser->userDeleteFriend($this->FID, $friendId);
            //环信聊天不需要加为好友,所以删除App好友的时候要把环信好友删除并且加入黑名单,才能不让他聊天
            $easeUser->userBlockUser($this->FID, $friendId);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }


    /**
     * 用户加入部门的环信群组
     *
     * @param Group|integer $group
     */
    public function joinHxGroup($group)
    {
        if (!($group instanceof Group)) {
            $group = Group::find($group);
        }

        if (empty($group->hx_group_id)) {
            //需求规定
            //部门的群聊不在创建的时候全部生成,
            //只有当第一个用户加入这个部门的时候,如果此时没有环信群聊,创建群聊
            $group->createChatGroupWithOwner($this->FID);
        } else {
            //如果进去部门的时候已经有了群聊,直接加入
            $easeUser = new EaseUser();

            $easeUser->groupAddUser($group->hx_group_id, $this->FID);

            //进去群聊之后,要给群聊发消息
            $easeUser->sendUserJoiningGroupMsg($this->FID, $group->hx_group_id);
        }
    }

    /**
     * 移交群主
     *
     * @param $hxGroupId
     * @param $newOwnerUserId
     *
     */
    public function transforOwnerToUser($hxGroupId, $newOwnerUserId)
    {
        $easeUser = new EaseUser();

        //环信移交群主之后,群主会被提出群聊
        $easeUser->transforGroupOwnerToUser($hxGroupId, $newOwnerUserId);

        //需要手动将群主重新加回群聊
        $easeUser->groupAddUser($hxGroupId, $this->FID);
    }

    /**
     * 把环信用户加入黑名单
     *
     * @param $blockUserId
     */
    public function blockHxUser($blockUserId)
    {
        (new EaseUser())->userBlockUser($this->FID, $blockUserId);
    }

    /**
     * 把环信用户移除黑名单
     *
     * @param $unBlockUserId
     */
    public function unblockHxUser($unBlockUserId)
    {
        (new EaseUser())->userUnBlockUser($this->FID, $unBlockUserId);
    }

    /**
     * 获取用户的环信黑名单
     */
    public function hxBlackLists()
    {
        return (new EaseUser())->userBlackLists($this->FID);
    }

}

