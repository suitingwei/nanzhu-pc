<?php

namespace App\Models;

use App\User;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Model;

/**
 * @property \App\User user
 */
class JoinGroup extends Model
{
    /**
     * 申请进组状态
     */
    const STATUS_WAIT_AUDIT   = 'WAIT_AUDIT';
    const STATUS_JOIN_SUCCESS = 'JOIN_SUCCESS';
    const STATUS_JOIN_FAIL    = 'JOIN_FAIL';

    public $table = 'join_group';

    public $fillable = ['id', 'user_id', 'movie_id', 'group_id', 'audit_status', 'audit_user_id', 'audit_at'];

    /**
     * 加入新的部门的时候(多部门)
     * 需要复制的新的权限
     * @var array
     */
    private $powerNeedToCopyWhenJoinNewGroup = [
        ContactPower::class,
        ProgressPower::class,
        ReceivePower::class
    ];

    /**
     * 申请进组没有被处理
     */
    public function hadNotHandled()
    {
        return $this->audit_status == self::STATUS_WAIT_AUDIT;
    }

    /**
     * 创建新的入组申请
     *
     * @param              $userId
     * @param array|string $groupIds
     * @param              $movieId
     *
     * @internal param Request $request
     */
    public static function createNewJoin($userId, $groupIds, $movieId)
    {
        if (!is_array($groupIds)) {
            $groupIds = explode(',', $groupIds);
        }

        foreach ($groupIds as $groupId) {
            self::create([
                'user_id'      => $userId,
                'movie_id'     => $movieId,
                'group_id'     => $groupId,
                'audit_status' => self::STATUS_WAIT_AUDIT
            ]);
        }
    }

    /**
     * 一个申请由一个用户创建
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'FID');
    }

    /**
     * 申请的组
     */
    public function group()
    {
        return $this->belongsTo(Group::class, 'group_id', 'FID');
    }

    /**
     * 申请的剧
     */
    public function movie()
    {
        return $this->belongsTo(Movie::class, 'movie_id', 'FID');
    }


    /**
     * 批准进组申请
     *
     * @param $userId
     */
    public function approvedByUser($userId)
    {
        self::where(['user_id' => $this->user_id, 'group_id' => $this->group_id])->update([
            'audit_status'  => self::STATUS_JOIN_SUCCESS,
            'audit_user_id' => $userId,
            'audit_at'      => Carbon::now()
        ]);

        //把这个用户添加到这个组
        $this->addUserToJoinGroup();
    }

    /**
     * 批准进组申请
     *
     * @param $userId
     */
    public function declinedByUser($userId)
    {
        //由于ios缓存导致的数据重复,所以申请进组会有多条重复数据
        self::where(['user_id' => $this->user_id, 'group_id' => $this->group_id])->update([
            'audit_status'  => self::STATUS_JOIN_FAIL,
            'audit_user_id' => $userId,
            'audit_at'      => Carbon::now()
        ]);
    }

    /**
     * 把当前用户添加到其申请的组
     */
    public function addUserToJoinGroup()
    {
        //复制用户加入的第一个部门的信息
        //防止由于网路原因多次加入同一个组
        $currentGroupUser = $this->user->groupUsersInMovie($this->movie_id)->first();

        DB::unprepared('LOCK TABLES t_biz_groupuser write,
		    t_biz_movie write,		    
		    t_sys_user  write,
			t_biz_group write,
	        profiles write,
            t_biz_contactpower write,
            t_biz_progresspower write, 
            t_biz_nereceivepower write'
        );
        if (!$this->user->isInGroup($this->group_id)) {

            //加入申请的部门
            $joinGroupUser = $this->joinNewDepartment($currentGroupUser);

            //添加权限
            $this->copyGroupUserPower($currentGroupUser, $joinGroupUser);

            //用户加入该部门的环信群聊
            $this->user->joinHxGroup($this->group_id);
        }

        DB::unprepared('UNLOCK TABLES');
    }

    /**
     * 当用户加入多部门的时候复制权限
     * 这样用户退出某一个部门之后仍然拥有该剧对应的权限,方便部门删除成员
     *
     * @param $oldGroupUser
     * @param $newGroupUser
     *
     * @internal param $currentGroupUser
     */
    private function copyGroupUserPower($oldGroupUser, $newGroupUser)
    {
        foreach ($this->powerNeedToCopyWhenJoinNewGroup as $powerNeedToCopy) {

            $oldPower = $powerNeedToCopy::where([
                'FGROUPUSERID' => $oldGroupUser->FID,
                'FMOVIEID'     => $this->movie_id
            ])->first();

            if ($oldPower) {
                $powerNeedToCopy::create([
                    'FID'          => $powerNeedToCopy::max('FID') + 1,
                    'FGROUPUSERID' => $newGroupUser->FID,
                    'FMOVIEID'     => $this->movie_id
                ]);
            }
        }

    }

    /**
     * 加入新的部门
     *
     * @param $currentGroupUser
     *
     * @return mixed
     */
    private function joinNewDepartment($currentGroupUser)
    {
        $joinGroupUser           = $currentGroupUser->replicate();
        $joinGroupUser->FID      = GroupUser::max('FID') + 1;
        $joinGroupUser->FGROUP   = $this->group_id;
        $joinGroupUser->FMOVIE   = $this->movie_id;
        $joinGroupUser->FNEWDATE = Carbon::now();
        $joinGroupUser->save();
        return $joinGroupUser;
    }

}
