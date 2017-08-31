<?php

namespace App\Models;

use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class EaseUser extends Model
{
    private $easemob;

    const USER_NAME_PREFIX                  = 'nanzhu_';
    const DEPARTMENT_CHAT_GROUP_NAME_PREFIX = 'nanzhu_group_';
    const CUSTOM_CHAT_GROUP_NAME_PREFIX     = 'nanzhu_custom_group';
    const MOVIE_CHAT_GROUP_NAME_PREFIX      = 'nanzhu_movie_group_';


    /**
     * EaseUser constructor.
     */
    public function __construct()
    {
        $options['client_id']     = env("EASEMOD_client_id");
        $options['client_secret'] = env("EASEMOD_client_secret");
        $options['org_name']      = env("EASEMOD_ORG_NAME");
        $options['app_name']      = env("EASEMOD_APP_NAME");
        $this->easemob            = new Easemob($options);
        \Log::info("--------");
    }

    /**
     * 注册环信用户
     * 账号密码都是: nanzhu_{user_id}
     *
     * @param $sys_user_id
     *
     * @return bool
     */
    public function register($sys_user_id)
    {
        $username = self::USER_NAME_PREFIX . $sys_user_id;
        $password = self::USER_NAME_PREFIX . $sys_user_id;
        $user     = User::where("FID", $sys_user_id)->first();
        if (!$user->easemob_uuid) {
            $result = $this->easemob->userAuthorizedRegister($username, $password);
            \Log::info($result['entities'][0]['uuid']);
            User::where("FID", $sys_user_id)->update(["easemob_uuid" => $result['entities'][0]['uuid']]);
            return true;
        }
        return false;
    }

    /**
     * 删除环信用户
     *
     * @param $userId
     */
    public function deleteUser($userId)
    {
        $username = self::USER_NAME_PREFIX . $userId;

        $this->easemob->userDelete($username);
    }

    /**
     * 获取用户信息
     *
     * @param $userId
     *
     * @return mixed
     */
    public function getUserInfo($userId)
    {
        $userName = self::USER_NAME_PREFIX . $userId;

        try {
            $responseData = $this->easemob->getUserInfo($userName);
            $userInfo     = $responseData['entities'][0];
        } catch (\Exception $e) {
            $userInfo = null;
        }

        return $userInfo;
    }

    /**
     * 判断用户是否登录
     *
     * @param $userId
     *
     * @return bool
     */
    public function isUserOnline($userId)
    {
        $userName = self::USER_NAME_PREFIX . $userId;

        return $this->easemob->userOnline($userName);
    }

    /**
     * 强制用户退出登录
     *
     * @param $userId
     *
     * @return mixed
     */
    public function forceUserDisconnect($userId)
    {
        $userName = self::USER_NAME_PREFIX . $userId;

        return $this->easemob->forceUserDisconnect($userName);
    }

    /**
     * 用户添加好友
     *
     * @param $ownerUserId
     * @param $friendUserId
     *
     * @return mixed
     */
    public function userAddFriend($ownerUserId, $friendUserId)
    {
        $ownerUserName  = self::USER_NAME_PREFIX . $ownerUserId;
        $friendUserName = self::USER_NAME_PREFIX . $friendUserId;

        return $this->easemob->userAddFriend($ownerUserName, $friendUserName);
    }

    /**
     * 用户删除好友
     *
     * @param $ownerUserId
     * @param $friendUserId
     *
     * @return mixed
     */
    public function userDeleteFriend($ownerUserId, $friendUserId)
    {
        $ownerUserName  = self::USER_NAME_PREFIX . $ownerUserId;
        $friendUserName = self::USER_NAME_PREFIX . $friendUserId;

        return $this->easemob->userDeleteFriend($ownerUserName, $friendUserName);
    }

    /**
     * 创建群组
     *
     * @param int  $ownerId     //群组的管理员，此属性为必须的
     * @param int  $chatGroupId /群组名称，此属性为必须的
     * @param      $desc
     * @param int  $maxUsers    /群组成员最大数（包括群主），值为数值类型，默认值200，最大值2000，此属性为可选的
     * @param bool $isPublic    /是否是公开群，此属性为必须的
     * @param bool $approval    /加入公开群是否需要批准，默认值是false（加入公开群不需要群主批准），此属性为必选的，私有群必须为true
     *
     * @return mixed
     */
    public function createDepartmentChatGroup(
        $ownerId,
        $chatGroupId,
        $desc = 'nanzhu chat group desc',
        $maxUsers = 500,
        $isPublic = true,
        $approval = true
    ) {
        $groupName = self::DEPARTMENT_CHAT_GROUP_NAME_PREFIX . $chatGroupId;

        return $this->easemob->createGroup($ownerId, $groupName, $desc, $maxUsers, $isPublic, $approval);
    }

    /**
     * 用户自己拉的群组
     *
     * @param        $ownerId
     * @param        $chatGroupId
     * @param string $desc
     *
     * @return mixed
     */
    public function createCustomChatGroup($ownerId, $chatGroupId, $desc = 'nanzhu chat group desc')
    {
        $groupName = self::CUSTOM_CHAT_GROUP_NAME_PREFIX . $chatGroupId;

        return $this->easemob->createGroup($ownerId, $groupName, $desc);
    }

    /**
     * @param $hxGroupId
     *
     * @return mixed
     */
    public function deleteGroup($hxGroupId)
    {
        Group::where('hx_group_id', $hxGroupId)->update(['hx_group_id' => '']);

        return $this->easemob->deleteGroup($hxGroupId);
    }

    /**
     * 获取环信群组信息
     *
     * @param $hxGroupId
     *
     * @param $currentUser
     *
     * @return mixed
     */
    public function getGroupInfo($hxGroupId, $currentUser)
    {
        $groupInfo = $this->easemob->getGroupInfo($hxGroupId)['data'][0];
        $group     = EaseUser::getGroupFromName($groupInfo['name']);

        //是否是部门群聊
        $isJuzuCreatedChatGroup = !empty($group);

        if (empty($group)) {
            $group = CustomHxGroup::where('hx_group_id', $hxGroupId)->first();
        }

        $owner = null;

        //获取群成员列表
        list($owner, $memberUsers) = $this->getFormattedMemberUsers($currentUser, $groupInfo);

        $result = [
            'is_juzu'       => $isJuzuCreatedChatGroup,
            'group_id'      => $groupInfo['id'],
            'title'         => $group->hx_title,
            'groupname'     => $groupInfo['name'],
            'members_count' => $groupInfo['affiliations_count'],
            'cover_url'     => Group::DEPARTMENT_HX_GROUP_COVER_URL,
            'type'          => Message::TYPE_CHAT_GROUP,
            'owner_user_id' => $owner->user_id,
            'is_owner'      => $owner->user_id== $currentUser->FID,
            'members'       => $memberUsers,
            'public_notice' => $this->getPublicNotice($groupInfo['id'])
        ];

        return $result;
    }

    /**
     * 获取群组群主
     *
     * @param $hxGroupId
     *
     * @return string
     */
    public function getGroupOwner($hxGroupId)
    {
        $groupInfo = $this->easemob->getGroupInfo($hxGroupId);

        $owner = array_column($groupInfo['data'][0]['affiliations'], 'owner')[0];

        return $owner;
    }

    /**
     * 更新环信群组信息
     *
     * @param      $hxGroupId
     * @param null $groupName
     * @param null $description
     * @param null $maxMembers
     *
     * @return mixed
     */
    public function updateGroupInfo($hxGroupId, $groupName = null, $description = null, $maxMembers = null)
    {
        return $this->easemob->updateGroupInfo($hxGroupId, $groupName, $description, $maxMembers);
    }

    /**
     * 创建剧组聊天群组
     *
     * @param int  $ownerId     //群组的管理员，此属性为必须的
     * @param int  $chatGroupId /群组名称，此属性为必须的
     * @param      $desc
     * @param int  $maxUsers    /群组成员最大数（包括群主），值为数值类型，默认值200，最大值2000，此属性为可选的
     * @param bool $isPublic    /是否是公开群，此属性为必须的
     * @param bool $approval    /加入公开群是否需要批准，默认值是false（加入公开群不需要群主批准），此属性为必选的，私有群必须为true
     *
     * @return mixed
     */
    public function createMovieChatGroup(
        $ownerId,
        $chatGroupId,
        $desc = 'nanzhu chat group desc',
        $maxUsers = 500,
        $isPublic = true,
        $approval = true
    ) {
        $groupName = self::MOVIE_CHAT_GROUP_NAME_PREFIX . $chatGroupId;

        return $this->easemob->createGroup($ownerId, $groupName, $desc, $maxUsers, $isPublic, $approval);
    }

    /**
     * 获取群组成员
     *
     * @param int $hxGroupId
     *
     * @return mixed
     */
    public function groupMembers($hxGroupId)
    {
        return $this->easemob->groupMembers($hxGroupId);
    }

    /**
     * 向群组添加成员
     *
     * @param int $hxGroupId
     * @param int $userId
     *
     * @return mixed
     */
    public function groupAddUser($hxGroupId, $userId)
    {
        $userName = self::USER_NAME_PREFIX . $userId;

        return $this->easemob->groupAddUser($hxGroupId, $userName);
    }

    /**
     * 向群组中添加多个成员
     *
     * @param int   $hxGroupId
     * @param array $userIds
     *
     * @return mixed
     */
    public function groupAddUsers($hxGroupId, $userIds = [])
    {
        $userNames = $this->transToUserNames($userIds);

        return $this->easemob->groupAddUsers($hxGroupId, $userNames);
    }

    /**
     * 从群组中删除成员
     *
     * @param $hxGroupId
     * @param $userId
     *
     * @return mixed
     */
    public function groupRemoveUser($hxGroupId, $userId)
    {
        $userName = self::USER_NAME_PREFIX . $userId;

        return $this->easemob->groupRemoveUser($hxGroupId, $userName);
    }

    /**
     * 从群组中删除多个成员
     *
     * @param int   $hxGroupId
     * @param array $userIds
     *
     * @return mixed
     */
    public function groupRemoveUsers($hxGroupId, $userIds = [])
    {
        $userNames = $this->transToUserNames($userIds);

        return $this->easemob->groupRemoveUsers($hxGroupId, $userNames);
    }

    /**
     * 用户参与的所有群组
     *
     * @param int $userId
     *
     * @return mixed
     */
    public function userJoinedGroups($userId)
    {
        $userName = self::USER_NAME_PREFIX . $userId;

        return $this->easemob->userJoinedGroups($userName);
    }

    /**
     * 转让群组
     *
     * @param int $hxGroupId
     * @param int $userId
     *
     * @return mixed
     */
    public function transforGroupOwnerToUser($hxGroupId, $userId)
    {
        $newOwnerName = self::USER_NAME_PREFIX . $userId;

        return $this->easemob->transforGroupOwnerToUser($hxGroupId, $newOwnerName);
    }

    /**
     * 群组的所有黑名单
     *
     * @param $groupId
     *
     * @return mixed
     */
    public function groupBlackLists($groupId)
    {
        $blackLists = $this->easemob->groupBlackLists($groupId)['data'];

        return array_map(function ($blackUserName) {
            $user = self::getUserFromName($blackUserName);
            if ($user) {
                return $user->formatBasicClass()->get();
            }
        }, $blackLists);
    }

    /**
     * 群组屏蔽一个用户
     *
     * @param $hxGroupId
     * @param $userId
     *
     * @return mixed
     */
    public function groupBlockUser($hxGroupId, $userId)
    {
        $userName = self::USER_NAME_PREFIX . $userId;

        return $this->easemob->groupBlockUser($hxGroupId, $userName);
    }

    /**
     * 群组屏蔽多个用户
     *
     * @param       $hxGroupId
     * @param array $userIds
     *
     * @return mixed
     *
     */
    public function groupBlockUsers($hxGroupId, $userIds = [])
    {
        $userNames = $this->transToUserNames($userIds);

        return $this->easemob->groupBlockUsers($hxGroupId, $userNames);
    }

    /**
     * 从群组黑名单删除一个用户
     *
     * @param $hxGroupId
     * @param $userId
     *
     * @return mixed
     */
    public function groupUnblockUser($hxGroupId, $userId)
    {
        $userName = self::USER_NAME_PREFIX . $userId;

        return $this->easemob->groupUnBlockUser($hxGroupId, $userName);
    }

    /**
     * 从群组黑名单删除多个用户
     *
     * @param int   $hxGroupId
     * @param array $userIds
     *
     * @return mixed
     */
    public function groupUnBlockUsers($hxGroupId, $userIds = [])
    {
        $userNames = $this->transToUserNames($userIds);

        return $this->easemob->groupUnblockUsers($hxGroupId, $userNames);
    }


    /**
     * 从环信username 获取 user_id
     *
     * @param $userName
     *
     * @return User
     */
    public static function getUserFromName($userName)
    {
        $userId = str_replace(EaseUser::USER_NAME_PREFIX, '', $userName);

        return User::find($userId);
    }

    /**
     * 用户加入黑名单
     *
     * @param $userId
     * @param $friendUserId
     *
     * @return mixed
     */
    public function userBlockUser($userId, $friendUserId)
    {
        $friendName = self::USER_NAME_PREFIX . $friendUserId;

        $userName = self::USER_NAME_PREFIX . $userId;

        return $this->easemob->userBlockUser($userName, $friendName);
    }

    /**
     * 用户把用户加入黑名单
     *
     * @param $userId
     * @param $friendUserIds
     *
     * @return mixed
     * @internal param string $ownerName
     * @internal param array $friendNames
     *
     */
    public function userBlockUsers($userId, $friendUserIds)
    {
        $friendNames = $this->transToUserNames($friendUserIds);

        $userName = self::USER_NAME_PREFIX . $userId;

        return $this->easemob->userBlockUsers($userName, $friendNames);
    }

    /**
     * 用户把人移除黑名单
     *
     * @param $userId
     * @param $friendUserId
     *
     * @return mixed
     */
    public function userUnBlockUser($userId, $friendUserId)
    {
        $friendName = self::USER_NAME_PREFIX . $friendUserId;

        $userName = self::USER_NAME_PREFIX . $userId;

        return $this->easemob->userUnBlockUser($userName, $friendName);
    }

    /**
     * 用户把人从黑名单移除
     *
     * @param $userId
     * @param $friendUserIds
     *
     */
    public function userUnBlockUsers($userId, $friendUserIds)
    {
        $friendNames = $this->transToUserNames($friendUserIds);

        $userName = self::USER_NAME_PREFIX . $userId;

        $this->easemob->userUnBlockUsers($userName, $friendNames);
    }

    /**
     * 用户的黑名单
     *
     * @param $userId
     *
     * @return mixed
     *
     */
    public function userBlackLists($userId)
    {
        $ownerName = self::USER_NAME_PREFIX . $userId;

        $blackLists = $this->easemob->userBlackLists($ownerName)['data'];

        return array_map(function ($blackUser) {
            return EaseUser::getUserFromName($blackUser)->formatBasicClass()->get();
        }, $blackLists);
    }

    /**
     * 从环信 groupName 获取 group_id
     *
     * @param $groupName
     *
     * @return Group
     */
    public static function getGroupFromName($groupName)
    {
        $groupId = str_replace(EaseUser::DEPARTMENT_CHAT_GROUP_NAME_PREFIX, '', $groupName);

        return Group::find($groupId);
    }

    /**
     * @param $hxGroupId
     *
     * @return \stdClass
     */
    private function getPublicNotice($hxGroupId)
    {
        //如果是部门群聊,
        $publicNotice = null;

        $group = Group::where('hx_group_id', $hxGroupId)->first();
        if ($group) {
            $publicNotice = $group->publicNotices()->first();
        } else {
            $customHxGroup = CustomHxGroup::where('hx_group_id', $hxGroupId)->first();
            if ($customHxGroup) {
                $publicNotice = $customHxGroup->publicNotices()->first();
            }
        }

        //前端需要,如果没有公告,返回一个假数据的
        if (is_null($publicNotice)) {
            $fakeGroupLeader            = new \stdClass();
            $fakeGroupLeader->user_id   = 0;
            $fakeGroupLeader->user_name = '暂无公告';
            $fakeGroupLeader->cover_url = User::DEFAULT_COVER_URL;
            $fakeGroupLeader->phone     = 0;

            $publicNotice             = new \stdClass();
            $publicNotice->id         = 0;
            $publicNotice->group_id   = 0;
            $publicNotice->editor     = $fakeGroupLeader;
            $publicNotice->editor_id  = 0;
            $publicNotice->content    = '';
            $publicNotice->created_at = Carbon::now()->toDateTimeString();
            $publicNotice->updated_at = Carbon::now()->toDateTimeString();
            return $publicNotice;
        }
        return $publicNotice;
    }

    /**
     * @param $currentUser
     * @param $groupInfo
     *
     * @return array
     */
    private function getFormattedMemberUsers($currentUser, $groupInfo)
    {
//        $memberUsers = array_map(function ($hxGroupMember) use ($currentUser, &$owner) {
//            $memberName = isset($hxGroupMember['owner']) ? $hxGroupMember['owner'] : $hxGroupMember['member'];
//            $member     = EaseUser::getUserFromName($memberName);
//
//            if (isset($hxGroupMember['owner'])) {
//                $owner = $member;
//            }
//
//            if ($member) {
//                return $member->formatBasicClass()->withFriendInfo($currentUser)->get();
//            }
//        }, $groupInfo['affiliations']);

        $memberUsers = [];
        $owner       = null;

        foreach ($groupInfo['affiliations'] as $hxGroupMember) {
            $memberName = isset($hxGroupMember['owner']) ? $hxGroupMember['owner'] : $hxGroupMember['member'];
            $member     = EaseUser::getUserFromName($memberName);

            if (!$member) {
                continue;
            }

            $member = $member->formatBasicClass()->withFriendInfo($currentUser)->get();

            if (isset($hxGroupMember['owner'])) {
                $owner = $member;
                array_unshift($memberUsers, $owner);
            } else {
                array_push($memberUsers, $member);
            }
        }

        return array($owner, $memberUsers);
    }

    /**
     * @param $userIds
     *
     * @return array
     */
    private function transToUserNames($userIds)
    {
        $userNames = [];

        foreach ($userIds as $userId) {
            $userNames [] = self::USER_NAME_PREFIX . $userId;
        }
        return $userNames;
    }

    /**
     * 发送消息到群组
     *
     * @param $from
     * @param $hxGroupId
     * @param $msg
     *
     * @return mixed
     */
    public function sendPassthrouhghMsgToGroup($from, $hxGroupId, $msg)
    {
        $from = self::USER_NAME_PREFIX . $from;

        return $this->easemob->sendPassthroughMsgToGroup($from, $hxGroupId, $msg);
    }

    /**
     * 发送创建群聊的消息
     *
     * @param $from
     * @param $hxGroupId
     *
     * @return mixed
     */
    public function sendUserCreateGroupMsg($from, $hxGroupId)
    {
        $user = User::find($from);

        return $this->sendTooltipMsg($from, $hxGroupId, "{$user->hx_name}创建了群聊");
    }

    /**
     * 发送用户加入部门群聊消息
     *
     * @param $from
     * @param $hxGroupId
     *
     * @return mixed
     */
    public function sendUserJoiningGroupMsg($from, $hxGroupId)
    {
        $user = User::find($from);

        return $this->sendTooltipMsg($from, $hxGroupId, "{$user->hx_name}加入了群聊");
    }

    /**
     * 发送用于前端ui显示的提示消息
     * -------------------------
     * 1.用户创建了某一个部门群聊
     * 2.用户加入了某一个部门的群聊
     *
     * @param $from
     * @param $hxGroupId
     * @param $msg
     *
     * @return mixed
     */
    private function sendTooltipMsg($from, $hxGroupId, $msg)
    {
        $from = self::USER_NAME_PREFIX . $from;

        $data['msg'] = $msg;

        $data['ext'] = [
            'system' => true,
            'encode' => true
        ];

        return $this->easemob->sendToGroups($hxGroupId, $from, $data);
    }

    /**
     * 判断环信群组是否用户创建的
     *
     * @param $groupName
     *
     * @return boolean
     */
    public static function isAppCreateGroup($groupName)
    {
        $groupId = str_replace(EaseUser::DEPARTMENT_CHAT_GROUP_NAME_PREFIX, '', $groupName);

        $group = Group::find($groupId);

        return empty($group);
    }

}
