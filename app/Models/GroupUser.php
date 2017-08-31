<?php

namespace App\Models;

use App\Traits\DeleteGroupUserTrait;
use App\Traits\ModelFindTrait;
use App\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

/**
 * @property mixed FOPEN       剧组通讯录
 * @property mixed FPUBLICTEl  公开电话
 * @property mixed user
 * @property mixed group
 * @property mixed FGROUPUSERROLE
 * @property mixed FID
 * @property mixed movie
 * @property mixed FREMARK
 */
class GroupUser extends Model
{
    use ModelFindTrait;

    const PHONE_PUBLIC  = 10;           //电话加入公开电话
    const PHONE_PRIVATE = 20;           //电话没有加入公开电话

    const PHONE_IN_CONTACTS     = 10;   //电话加入剧组通讯录
    const PHONE_NOT_IN_CONTACTS = 20;   //电话没有加入剧组通讯录

    const PHONE_OPENED     = 1;         //共享电话是否被勾选
    const PHONE_NOT_OPENED = 0;         //共享电话没有被勾选

    const ROLE_ADMIN = 10;              //是某一个电影的最高权限者

    /**
     * 用户退出部门时候需要移除的权限
     *
     * @var array
     */
    protected $powerNeedToBeRemoved = [
        DailyReceive::class,          # 删除每日通告单接收人
        PreReceive::class,            # 删除预备通告单接收人
        CrewNotificationUser::class,  # 删除剧组通知人员表
        ReceivePower::class,          # 删除通告单接收详情权限
        ContactPower::class,          # 删除通联表权限
        ProgressPower::class,         # 删除拍摄进度查看权限
    ];

    public $timestamps = false;

    protected $table = "t_biz_groupuser";

    protected $guarded = [];

    /**
     * 一个组员是一个用户
     *
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'FUSER', 'FID');
    }

    /**
     * 一个组员属于一个部门
     *
     * @return BelongsTo
     */
    public function group()
    {
        return $this->belongsTo(Group::class, 'FGROUP', 'FID');
    }

    /**
     * 一个组员属于一个剧组
     *
     * @return BelongsTo
     */
    public function movie()
    {
        return $this->belongsTo(Movie::class, 'FMOVIE', 'FID');
    }

    /**
     * 组员拥有多个共享电话
     *
     * @return HasMany
     */
    public function sharePhones()
    {
        return $this->hasMany(SparePhone::class, 'FGROUPUSERID', 'FID');
    }

    /**
     * 判断一个组员是不是统筹组
     *
     * @param $movie_id
     * @param $user_id
     *
     * @return bool
     */
    public static function is_tongchou($movie_id, $user_id)
    {
        $data = \DB::table("t_biz_groupuser")->leftJoin('t_biz_group', 't_biz_groupuser.FGROUP', '=', 't_biz_group.FID')
                   ->where("t_biz_group.FMOVIE", $movie_id)
                   ->where("t_biz_group.FNAME", "like", "统筹%")
                   ->where("t_biz_groupuser.FUSER", $user_id)
                   ->first();
        if ($data) {
            return true;
        }

        return false;
    }


    /**
     * 判断一个组员是不是制片组
     *
     * @param $movie_id
     * @param $user_id
     *
     * @return bool
     */
    public static function is_zhipian($movie_id, $user_id)
    {
        $data = \DB::table("t_biz_groupuser")->leftJoin('t_biz_group', 't_biz_groupuser.FGROUP', '=', 't_biz_group.FID')
                   ->where("t_biz_group.FMOVIE", $movie_id)
                   ->where("t_biz_group.FNAME", "like", "制片%")
                   ->where("t_biz_groupuser.FUSER", $user_id)
                   ->first();
        if ($data) {
            return true;
        }

        return false;
    }

    /**
     * 判断一个组员是否是某一个剧组的导演组
     *
     * @param $movie_id
     * @param $user_id
     *
     * @return bool
     */
    public static function is_director($movie_id, $user_id)
    {
        return \DB::table("t_biz_groupuser")->leftJoin('t_biz_group', 't_biz_groupuser.FGROUP', '=', 't_biz_group.FID')
                  ->where("t_biz_group.FMOVIE", $movie_id)
                  ->where("t_biz_group.FNAME", "like", "导演%")
                  ->where("t_biz_groupuser.FUSER", $user_id)
                  ->count() > 0;
    }

    /**
     * 组员是不是统筹组
     * @return boolean
     */
    public function isTongChou()
    {
        return mb_strpos($this->group->FNAME, '统筹') !== false;
    }

    /**
     * 组员是不是统筹组
     * @return boolean
     */
    public function isZhiPian()
    {
        return mb_strpos($this->group->FNAME, '制片') !== false;
    }

    /**
     * 组员是不是统筹组
     * @return boolean
     */
    public function isDirector()
    {
        return mb_strpos($this->group->FNAME, '导演') !== false;
    }

    /**
     * 将电话设置成私有
     */
    public function setPhonePrivate()
    {
        DB::table($this->table)->where(['FID' => $this->FID])->update(['FPUBLICTEL' => self::PHONE_PRIVATE]);
    }

    /**
     * 将电话设置成公开
     */
    public function setPhonePublic()
    {
        DB::table($this->table)->where(['FID' => $this->FID])->update(['FPUBLICTEL' => self::PHONE_PUBLIC]);
    }

    /**
     * 组员是否被赋予剧组的剧组通讯录查看权限
     *
     * @param  int $movieID
     *
     * @return bool
     */
    public function isAssignedContactPower($movieID)
    {
        return ContactPower::where(['FGROUPUSERID' => $this->FID, 'FMOVIEID' => $movieID])->count() > 0;
    }

    /**
     * 组员是否被赋予剧组的拍摄进度查看权限
     *
     * @param  int $movieID
     *
     * @return bool
     */
    public function isAssignedProgressPower($movieID)
    {
        return ProgressPower::where(['FGROUPUSERID' => $this->FID, 'FMOVIEID' => $movieID])->count() > 0;
    }

    /**
     * 组员是否被赋予剧组的接受详情录查看权限
     *
     * @param  int $movieID
     *
     * @return bool
     */
    public function isAssignedReceivePowerInMovie($movieID)
    {
        return ReceivePower::where(['FGROUPUSERID' => $this->FID, 'FMOVIEID' => $movieID])->count() > 0;
    }

    /**
     * 将某一个groupuser指定为admin
     */
    public function assignToAdmin()
    {
        DB::table($this->table)->where(['FID' => $this->FID])->update(['FGROUPUSERROLE' => Movie::ROLE_ADMIN]);
    }

    /**
     * 将某一个GroupUser指定为普通用户
     */
    public function assignToCommonUser()
    {
        DB::table($this->table)->where(['FID' => $this->FID])->update(['FGROUPUSERROLE' => Movie::ROLE_COMMON_USER]);
    }

    /**
     * groupuser是否具有对某个movie的进度权限
     *
     * @param $movieId
     *
     * @return bool
     */
    public function notHavProgressPowerInMovie($movieId)
    {
        return ProgressPower::where(['FMOVIEID' => $movieId, 'FGROUPUSERID' => $this->FID])->count() <= 0;
    }

    /**
     * 组员是否加入剧组通讯录
     *
     * @return boolean
     */
    public function hadJoinedContacts()
    {
        if (is_null($this->FOPEN)) {
            return false;
        }

        return $this->FOPEN == self::PHONE_IN_CONTACTS;
    }

    /**
     * 组员是否加入公开电话
     *
     * @return boolean
     */
    public function hadJoinedPublicContacts()
    {
        if (is_null($this->FPUBLICTEL)) {
            return false;
        }

        return $this->FPUBLICTEL == self::PHONE_PUBLIC;
    }

    /**
     * 把部门成员添加到剧组通讯录
     */
    public function joinContacts()
    {
        DB::table($this->table)->where(['FID' => $this->FID])->update(['FOPEN' => self::PHONE_IN_CONTACTS]);
    }

    /**
     * 把部门成员从剧组通讯录移除
     */
    public function removeContacts()
    {
        DB::table($this->table)
          ->where(['FID' => $this->FID])
          ->update([
              'FOPEN'      => self::PHONE_NOT_IN_CONTACTS,
              'FPUBLICTEL' => self::PHONE_PRIVATE
          ]);
    }

    /**
     * 因为不能使用主键primarykey,会影响老代码
     * 原有delete方法无法正常执行
     * 为了方便使用delete,只能替换成该方法
     */
    public function remove()
    {
        DB::table($this->table)->where(['FID' => $this->FID])->delete();
    }

    /**
     * 判断一个组员是否可以被删除
     * 1.不是最高权限管理员
     * 2.不是部门长
     */
    public function canBeDeleted()
    {
        return $this->isNotAdmin();
    }

    /**
     * 判断一个组员是否最高权限管理者
     * @return  boolean
     */
    public function isNotAdmin()
    {
        return !$this->isAdmin();
    }

    /**
     * 判断一个组员是否最高权限管理者
     * @return  boolean
     */
    public function isAdmin()
    {
        return $this->FGROUPUSERROLE == Movie::ROLE_ADMIN;
    }

    /**
     * 组员是否是该组组长
     * @return  boolean
     */
    public function isLeader()
    {
        return $this->user->isLeaderOfGroup($this->group);
    }

    /**
     * 判断组员是不是组长
     * @return bool
     */
    public function isNotLeader()
    {
        return !$this->isLeader();
    }


    /**
     * 获取组员职位
     * @return mixed|string
     */
    public function getPositionAttribute()
    {
        return $this->FREMARK ? $this->FREMARK : '暂未填写职位';
    }

    /**
     * 获取groupuser的照片
     * @return mixed
     */
    public function getUserPicUrlAttribute()
    {
        //保证groupuser所属的user存在,user含有个人资料
        if ($this->user && $this->user->profile) {
            return $this->user->profile->avatar;
        }
        return '';
    }

    /**
     * 电话是否公开
     */
    public function isPhoneOpened()
    {
        return $this->FOPENED == self::PHONE_OPENED;
    }

    /**
     * 获取我在本组的电话信息
     * 取三条,不足以空不全
     *
     * @return array
     */
    public function sharePhonesInGroup()
    {
        return SparePhone::where('FGROUPUSERID', $this->FID)->select(
            'FID as spare_phone_id',
            'FChecked as is_open',
            'FREGPHONE as is_register_phone',
            'FPHONE as phone_number',
            'FPOS as order'
        )->orderBy('FPOS')->take(3)->get();
    }

    /**
     * 组员退出当前的组
     */
    public function exitGroup()
    {
        DB::transaction(function () {
            //删除所有权限
            $this->getRidOfAllPower();

            //移交可能有的共享电话
            $this->transforSharePhones();

            //删除所有在这个剧组的申请加入部门的记录,防止下次再加入的时候仍然显示
            $this->removeJoinGroupRecord();

            //删除所有剧组通知,剧本扉页的消息
            $this->removeMessageRecord();

            //退出剧组
            $this->remove();
        });
    }


    /**
     * 剥夺组员的所有权限
     */
    private function getRidOfAllPower()
    {
        array_map(function ($power) {
            if (class_use_trait($power, DeleteGroupUserTrait::class)) {
                $power::removeGroupUser($this->FID);
            }
        }, $this->powerNeedToBeRemoved);

        //如果是部门长移除部门长
        if ($this->isLeader()) {
            $this->removeLeaderPower();
        }
    }

    /**
     * 如果是部门长退出部门,移除
     */
    private function removeLeaderPower()
    {
        Group::where(['FID' => $this->group->FID])->update(['FLEADERID' => null]);
    }

    /**
     * 如果用户存在多个组.
     * 退出第一个组的话,把当前共享的电话信息转交给下一个组
     */
    private function transforSharePhones()
    {
        //该组员在当前剧组所在的所有部门组员
        $groupUsers = $this->allGroupUsers();

        //如果就一个部门,退出的时候,删除电弧
        if ($groupUsers->count() <= 1) {
            SparePhone::where('FGROUPUSERID', $this->FID)->delete();
            return;
        }

        //如果用户退出的是他加入的第一个组,需要将关联的电话信息转移到下一个剧组
        if ($this->FID === $groupUsers->first()->FID) {
            $this->sharePhones()->update(['FGROUPUSERID' => $groupUsers[1]->FID]);
        }
    }

    /**
     * 删除所有在这个剧组的申请加入部门的记录
     */
    private function removeJoinGroupRecord()
    {
        JoinGroup::where(['movie_id' => $this->movie->FID, 'user_id' => $this->user->FID])->delete();
    }

    /**
     * 删除所有这个人的接受通知
     */
    private function removeMessageRecord()
    {
        //如果这个人只有这一个部门代表这个人正在退出剧组
        if ($this->user->groupsInMovie($this->movie->FID)->count() == 1) {

            //这个剧组里用户的所有的所有通知
            $messages = Message::where(['movie_id' => $this->movie->FID])->where('scope_ids', 'like', '%' . $this->user->FID . '%')->get();

            foreach ($messages as $message) {
                $message->removeUserFromReceivers($this->user->FID);
            }
        }

    }

    /**
     * 当前组员在当前剧组所属的其他部门
     *
     * @return Collection
     */
    public function otherGroupUsers()
    {
        return self::where(['FMOVIE' => $this->movie->FID, 'FUSER' => $this->user->FID])->where('FID', '!=',
            $this->FID)->get();
    }

    /**
     * 当前组员在当前剧组所属的所有部门
     *
     * @return Collection
     */
    public function allGroupUsers()
    {
        return self::where(['FMOVIE' => $this->movie->FID, 'FUSER' => $this->user->FID])->orderBy('FNEWDATE')->get();
    }


    /**
     * 删除原admin的进度权限
     */
    public function removeOldAdminProgressPower()
    {
        //删除原admin的进度权限
        $oldAdminProgressPower = ProgressPower::where([
            'FMOVIEID'     => $this->movie->FID,
            'FGROUPUSERID' => $this->FID
        ])->first();

        if ($oldAdminProgressPower) {
            $oldAdminProgressPower->delete();
        }
    }

}

