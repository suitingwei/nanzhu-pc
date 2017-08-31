<?php

namespace App\Traits\User;

use App\Exceptions\ExitGroupNotAllowedException;
use App\Models\Group;
use App\Models\GroupUser;
use App\Models\JoinGroup;
use App\Models\Movie;
use App\Models\SparePhone;
use DB;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * ----------------------------------------
 * 请不要将该trait用于除了User之外的任何地方!!!
 * 该trait只用于分解用户有关部门的操作
 * ----------------------------------------
 *
 * Class GroupOperationTrait
 * @package App\Traits\User
 */
trait GroupOperationTrait
{

    /**
     * 用户管理的所有部门
     * @return HasMany
     */
    public function leaderGroups()
    {
        return $this->hasMany(Group::class, 'FLEADERID', 'FID');
    }

    /**
     * 用户在当前剧组管理的所有部门
     *
     * @param int $movieId
     *
     * @return Collection
     */
    public function leaderGroupsInMovie($movieId)
    {
        return $this->leaderGroups()->where('FMOVIE', $movieId)->get();
    }

    /**
     * 用户所在的所有部门
     *
     * @return Collection
     */
    public function groups()
    {
        $groupIds = GroupUser::where('FUSER', $this->FID)->lists('FGROUP');

        return Group::whereIn('FID', $groupIds)->get();
    }

    /**
     * 用户在当前这个剧组的所有部门
     * ------------------------------
     * 1. 按照用户加入部门的先后时间排序
     *
     * @param $movieId
     *
     * @return Collection
     */
    public function groupsInMovie($movieId)
    {
        $groupIds = GroupUser::where([
            'FUSER'  => $this->FID,
            'FMOVIE' => $movieId
        ])->orderBy('FNEWDATE')->lists('FGROUP');

        return Group::whereIn('FID', $groupIds)->get();
    }

    /**
     * 用户在这个剧组中加入的第一个部门
     *
     * @param $movieId
     *
     * @return mixed
     */
    public function firstGroupInMovie($movieId)
    {
        return $this->firstGroupUserInMovie($movieId)->group;
    }

    /**
     * 用户是否在某一个部门
     *
     * @param $groupId
     *
     * @return bool
     */
    public function isInGroup($groupId)
    {
        return GroupUser::where(['FGROUP' => $groupId, 'FUSER' => $this->FID])->count() > 0;
    }

    /**
     * 用户是否申请过加入该部门
     *
     * @param $groupId
     *
     * @return bool
     */
    public function hadTryJoinedGroup($groupId)
    {
        return JoinGroup::where(['group_id' => $groupId, 'user_id' => $this->FID])->count() > 0;
    }

    /**
     * 用户是否没有申请过加入该部门
     *
     * @param $groupId
     *
     * @return bool
     */
    public function hadNotTryJoinedGroup($groupId)
    {
        return !$this->hadTryJoinedGroup($groupId);
    }

    /**
     * 用户的申请是否正在审核
     *
     * @param $groupId
     *
     * @return bool
     */
    public function isJoinGroupAuditting($groupId)
    {
        $joinGroup = JoinGroup::where(['group_id' => $groupId, 'user_id' => $this->FID])->orderBy('created_at',
            'desc')->first();

        if ($joinGroup) {
            return $joinGroup->audit_status == JoinGroup::STATUS_WAIT_AUDIT;
        }

        return false;
    }

    /**
     * 用户的申请是否处理了
     *
     * @param $groupId
     *
     * @return bool
     */
    public function isJoinGroupAuditted($groupId)
    {
        $joinGroup = JoinGroup::where(['group_id' => $groupId, 'user_id' => $this->FID])->orderBy('created_at',
            'desc')->first();

        if ($joinGroup) {
            return in_array($joinGroup->audit_status, [JoinGroup::STATUS_JOIN_FAIL, JoinGroup::STATUS_JOIN_SUCCESS]);
        }
        return false;
    }

    /**
     * 用户发出申请加入某部门
     * -----------------------------------------
     * 由于ios的缓存问题,他们发出的http请求都是同时2条
     * 所以需要锁表,避免重复
     *
     * @param $groupIds
     * @param $movieId
     *
     */
    public function tryJoinMovieGroup($groupIds, $movieId)
    {
        DB::unprepared('LOCK TABLES join_group write');
        //没申请过,可以直接申请
        if ($this->hadNotTryJoinedGroup($groupIds)) {
            JoinGroup::createNewJoin($this->FID, $groupIds, $movieId);
        }

        //申请过,且被处理了,也可以继续申请
        if ($this->hadTryJoinedGroup($groupIds) && $this->isJoinGroupAuditted($groupIds)) {
            JoinGroup::createNewJoin($this->FID, $groupIds, $movieId);
        }
        DB::unprepared('UNLOCK TABLES');
    }

    /**
     * 用户退出某一个部门
     * --------------------------------------------------
     * 1.如果只有一个部门,不允许退出
     * 2.如果有多个部门,当用户退出该部门,电话信息转移到下一个组
     *
     * @param $groupId
     * @param $movieId
     *
     * @throws ExitGroupNotAllowedException
     */

    public function exitGroup($groupId, $movieId)
    {
        if ($this->groupsInMovie($movieId)->count() == 1) {
            throw new  ExitGroupNotAllowedException('无法退出您的最后一个部门!');
        }

        //找到要退出的组员
        $groupUser = $this->groupUsers()->where('FGROUP', $groupId)->first();

        //让他滚
        $groupUser->exitGroup();
    }


    /**
     * 判断一个用户是否某一个小组组长
     *
     * @param Group|integer $group
     *
     * @return bool
     */
    public function isLeaderOfGroup($group)
    {
        if (!($group instanceof Group)) {
            $group = Group::find($group);
        }

        //避免错误数据导致报错,之前的部门列表删除的时候可能部门成员没有删除干净,导致groupuser存在但是对应的Group不存在了
        if (!$group) {
            return false;
        }

        $leader = $group->leadUser();

        if (is_null($leader)) {
            return false;
        }

        return $this->FID == $leader->FID;
    }

    /**
     * 获取当前组员的所在组字符串
     * 由于组员可能同时在多个组,所以不能信赖Groupuser->group->FNAME
     * ---------------------------------------------
     * 1.剧组通讯录只显示加入通讯录的部门的名字
     * 2.我在本组显示当前仍在的部门的名字
     *
     * @param      $movieId
     * @param bool $selectJoinedContacts
     *
     * @return string
     */
    public function groupNamesInMovie($movieId, $selectJoinedContacts = false)
    {
        $groupUsers = $this->groupUsersInMovie($movieId);

        $groupNames = [];
        foreach ($groupUsers->all() as $groupUser) {
            //防止因为删除部门时候没有删除组员导致的bug
            if (!$groupUser->group) {
                continue;
            }

            //选择加入通讯录的
            if (!$selectJoinedContacts) {
                $groupNames[] = $groupUser->group->FNAME;
            } else {
                if ($groupUser->hadJoinedContacts()) {
                    $groupNames [] = $groupUser->group->FNAME;
                }
            }
        }

        if (count($groupNames) == 1) {
            return $groupNames[0];
        }

        return implode('/', $groupNames);
    }

    /**
     * 更新用户在本组的信息
     *
     * @param $newData
     */
    public function updateGroupInfo($newData)
    {
        if (isset($newData['user_name'])) {
            DB::table('t_sys_user')->where('FID', $this->FID)->update(['FNAME' => $newData['user_name']]);
        }

        $updateData = [];

        if (isset($newData['position'])) {
            $updateData ['FREMARK'] = $newData['position'];
        }
        if (isset($newData['job_is_open'])) {
            $updateData ['FOPENED'] = $newData['job_is_open'];
        }

        if (isset($newData['room'])) {
            $updateData ['room'] = $newData['room'];
        }

        DB::table("t_biz_groupuser")
          ->where(['FMOVIE' => $newData['movie_id'], 'FUSER' => $this->FID])
          ->update($updateData);

        //更新SparePhone表
        foreach ($newData['phoneJson'] as $phoneInfo) {
            $phoneInfo = json_decode($phoneInfo);
            if ($phoneInfo) {
                SparePhone::find($phoneInfo->spare_phone_id)->update([
                    'FChecked' => $phoneInfo->is_open ? 1 : 0,
                    'FPHONE'   => $phoneInfo->phone_number
                ]);
            }
        }
    }

    /**
     * 加入部门并且角色是剧组的最高权限
     *
     * @param Group  $group
     * @param string $position
     *
     * @return GroupUser
     */
    public function joinGroupAsAdmin(Group $group, $position = '')
    {
        return GroupUser::create([
            'FID'            => GroupUser::max("FID") + 1,
            'FUSER'          => $this->FID,
            'FGROUP'         => $group->FID,
            'FMOVIE'         => $group->movie->FID,
            'FREMARK'        => $position,
            'FGROUPUSERROLE' => Movie::ROLE_ADMIN,
            'FOPEN'          => 10,
            'FOPENED'        => 1,
            'FPUBLICTEL'     => 20,
            'FNEWDATE'       => date('Y-m-d H:i:s'),
            'FEDITDATE'      => date('Y-m-d H:i:s'),
        ]);

    }

}
