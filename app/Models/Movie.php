<?php

namespace App\Models;

use App\Traits\ModelFindTrait;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Class Movie
 * @property mixed FID
 * @package App\Models
 */
class Movie extends Model
{
    use ModelFindTrait;

    const ROLE_ADMIN       = 10;       //最高管理者
    const ROLE_COMMON_USER = 40;      //普通用户

    const COUNT_UNIQUE_USER = true;

    /**
     * 创建剧必须的部门
     * @var array
     */
    public static $essentialDepartments = [
        ['name' => '制片', 'type' => Group::TYPE_ZHI_PIAN],
        ['name' => '统筹', 'type' => Group::TYPE_TONG_CHOU],
        ['name' => '导演', 'type' => Group::TYPE_DIRECTOR]
    ];

    protected $table = 't_biz_movie';

    protected $fillable = [
        'FID',
        'FNAME',
        'FNEWUSER',
        'FNEWDATE',
        'FTYPE',
        'FSTARTDATE',
        'FENDDATE',
        'FPASSWORD',
        'FISOROPEN',
        "chupinfang",
        "zhizuofang",
        'hx_group_id'
    ];

    public $timestamps = false;

    public $incrementing = false;

    protected $lastProgessDay = null;

    /**
     * 每一个剧组有一个总数据
     */
    public function totalData()
    {
        return $this->hasOne(ProgressTotalData::class, 'FMOVIEID', 'FID');
    }

    /**
     * @return array
     */
    public static function types()
    {
        return ["院线电影", "电视剧", "综艺", "网络大电影", "网剧", "广告", "演唱会", "舞台剧", " 纪录片"];
    }

    /**
     * @return array
     */
    public static function old_types()
    {
        return [
            "10"  => "院线电影",
            "20"  => "电视剧",
            "30"  => "综艺",
            "35"  => "数字电影",
            "40"  => "网络大电影",
            "50"  => "网剧",
            "60"  => "广告",
            "70"  => "演唱会",
            "80"  => "舞台剧",
            "90"  => "纪录片",
            "100" => "短片"
        ];
    }

    /**
     * 剧组有多个部门
     */
    public function groups()
    {
        return $this->hasMany(Group::class, 'FMOVIE', 'FID');
    }

    /**
     * 一个剧组有一个制片部门
     */
    public function zhiPianGroup()
    {
        return $this->hasMany(Group::class, 'FMOVIE', 'FID')->where('FGROUPTYPE', Group::TYPE_ZHI_PIAN)->first();
    }

    /**
     * 一个剧所有人
     *
     * @param bool $unique
     *
     * @return
     */
    public function allMembersCount($unique = true)
    {
        $groupUsers = GroupUser::where('FMOVIE', $this->FID)->lists('FUSER');

        if ($unique) {
            return $groupUsers->unique()->count();
        }

        return $groupUsers->count();
    }

    /**
     * 剧组所有用户
     *
     * @return Collection
     */
    public function allUsersInMovie()
    {
        $distinctUserIdArray = GroupUser::where([
            't_biz_groupuser.FMOVIE' => $this->FID
        ])->selectRaw('distinct FUSER')->lists('FUSER');

        return User::whereIn('FID', $distinctUserIdArray)->get();
    }

    /**
     * 剧组通讯录里的所有用户
     * 由于现在一个用户可能有多个组员身份.
     * 所以groupUser里可能有重复的user_id
     * 需要过滤.
     * ----------------------------------
     * @return Collection
     */
    public function allUsersInContacts()
    {
        $distinctUserIdArray = GroupUser::where([
            't_biz_groupuser.FMOVIE' => $this->FID,
            't_biz_groupuser.FOPEN'  => GroupUser::PHONE_IN_CONTACTS
        ])->selectRaw('distinct FUSER')->lists('FUSER');

        return User::whereIn('FID', $distinctUserIdArray)->get();
    }


    /**
     * 加入剧组公开电话的所有用户
     *
     * @return Collection
     */
    public function allUsersInPublicContacts()
    {
        $distinctUserIdArray = GroupUser::where([
            't_biz_groupuser.FMOVIE'     => $this->FID,
            't_biz_groupuser.FPUBLICTEL' => GroupUser::PHONE_PUBLIC
        ])->selectRaw('distinct FUSER')->lists('FUSER');

        return User::whereIn('FID', $distinctUserIdArray)->get();
    }

    /**
     * 赋予查看剧组通讯录权限的用户
     *
     * @return Collection
     */
    public function allUsersWithContactPower()
    {
        $distinctUserIdArray = GroupUser::where(['t_biz_contactpower.FMOVIEID' => $this->FID])
                                        ->join('t_biz_contactpower', 't_biz_groupuser.FID', '=',
                                            't_biz_contactpower.FGROUPUSERID')
                                        ->selectRaw('distinct t_biz_groupuser.FUSER')
                                        ->lists('FUSER');

        return User::whereIn('FID', $distinctUserIdArray)->get();
    }

    /**
     * 赋予查看剧组拍摄进度权限的用户
     *
     * @return Collection
     */
    public function allUsersWithProgressPower()
    {
        $distinctUserIdArray = GroupUser::where(['t_biz_progresspower.FMOVIEID' => $this->FID])
                                        ->join('t_biz_progresspower', 't_biz_groupuser.FID', '=',
                                            't_biz_progresspower.FGROUPUSERID')
                                        ->selectRaw('distinct t_biz_groupuser.FUSER')
                                        ->lists('FUSER');

        return User::whereIn('FID', $distinctUserIdArray)->get();
    }


    /**
     * 所有赋予接受详情权限的用户
     *
     * @return Collection
     */
    public function allUsersWithReceivePower()
    {
        $distinctUserIdArray = GroupUser::where(['t_biz_nereceivepower.FMOVIEID' => $this->FID])
                                        ->join('t_biz_nereceivepower', 't_biz_groupuser.FID', '=',
                                            't_biz_nereceivepower.FGROUPUSERID')
                                        ->selectRaw('distinct t_biz_groupuser.FUSER')
                                        ->lists('FUSER');

        return User::whereIn('FID', $distinctUserIdArray)->get();
    }

    /**
     * 剧组的最高权限管理员
     */
    public function admin()
    {
        $admin = GroupUser::where(['FMOVIE' => $this->FID, 'FGROUPUSERROLE' => self::ROLE_ADMIN])->first();

        return $admin ? $admin->user : null;
    }

    /**
     * 交换最高权限到另一个人
     *
     * @param $userId
     *
     * @internal param $movieId
     */
    public function transforAdminToUser($userId)
    {
        DB::transaction(function () use ($userId) {
            $this->removeOldAdminPower();

            $this->assignUserToAdmin($userId);
        });
    }

    /**
     * 去除原最高管理者权限
     */
    public function removeOldAdminPower()
    {
        //在groupuser设置原admin为普通用户
        $oldAdmins = GroupUser::where(['FGROUPUSERROLE' => self::ROLE_ADMIN, 'FMOVIE' => $this->FID])->get();

        foreach ($oldAdmins as $oldAdmin) {
            if ($oldAdmin) {
                $oldAdmin->assignToCommonUser();

                $oldAdmin->removeOldAdminProgressPower();
            }
        }
    }

    /**
     * 将指定人更新为最高管理者
     *
     * @param $userId
     */
    public function assignUserToAdmin($userId)
    {
        $user       = User::find($userId);
        $groupUsers = $user->groupUsersInMovie($this->FID);

        foreach ($groupUsers as $groupUser) {

            //查看新最高权限者是否已经有进度权限,
            if ($groupUser->notHavProgressPowerInMovie($this->FID)) {
                //如果没有添加
                ProgressPower::assignToGroupuserInMovie($groupUser->FID, $this->FID);
            }

            $groupUser->assignToAdmin();
        }
    }


    /**
     * 之前剧组的每日数据是否都填写了(0)也算有
     * 1.上一个拍摄日期等于昨天
     * 2.上一个拍摄日期大于昨天
     *
     * @param $currentDay
     *
     * @return bool
     */
    public function isAllPastDaysProgressDataFullfiled($currentDay)
    {
        $currentDay = Carbon::createFromTimestamp(strtotime($currentDay));

        $lastProgressedDay = DB::table('t_biz_progressdailydata')->select('FDATE')
                               ->where('FMOVIEID', $this->FID)
                               ->orderBy('FDATE', 'desc')
                               ->first();
        //如果之前没有拍过
        if (!$lastProgressedDay) {
            //判断今天是不是拍摄日期
            $startDate = DB::table("t_biz_progresstotaldata")->where("FMOVIEID", $this->FID)->first()->FSTARTDATE;

            return $currentDay->timestamp == strtotime($startDate);
        }

        $lastProgressedDay = Carbon::createFromTimestamp(strtotime($lastProgressedDay->FDATE));

        $lastDay = $currentDay->subDay();

        return ($lastDay->lte($lastProgressedDay));
    }

    /**
     * 获取上一次记录每日数据的日期
     * 1.如果之前拍过,返回最后一天拍摄日期
     * 2.如果没有拍过,返回开拍日期
     */
    public function getNeedToProgressDay()
    {
        $lastProgressedDay = DB::table('t_biz_progressdailydata')->select('FDATE')
                               ->where('FMOVIEID', $this->FID)
                               ->orderBy('FDATE', 'desc')
                               ->first();
        if (!$lastProgressedDay) {
            $totaldata = DB::table("t_biz_progresstotaldata")->where("FMOVIEID", $this->FID)->first();
            return Carbon::createFromTimestamp(strtotime($totaldata->FSTARTDATE))->toDateString();
        }

        return Carbon::createFromTimestamp(strtotime($lastProgressedDay->FDATE))->addDay()->toDateString();
    }


    /**
     * 剧组通讯录赋予权限的人/所有的人
     * @return string
     */
    public function contactPercent()
    {
        $contactPowerCount = $this->allUsersWithContactPower()->count();

        return "{$contactPowerCount}/{$this->allMembersCount()}";
    }


    /**
     * 剧组通讯录赋予权限的人/所有的人
     * @return string
     */
    public function progressPercent()
    {
        $progressCount = $this->allUsersWithProgressPower()->count();

        return "{$progressCount}/{$this->allMembersCount()}";
    }

    /**
     * 剧组通讯录赋予权限的人/所有的人
     * @return string
     */
    public function receivePercent()
    {
        $receiveCount = $this->allUsersWithReceivePower()->count();

        return "{$receiveCount}/{$this->allMembersCount()}";
    }


    /**
     * 创建环信聊天室
     *
     * @param User $user
     */
    public function createChatGroupWithOwner(User $user)
    {
        $returnData = (new EaseUser())->createMovieChatGroup($user->FID, $this->FID, $this->FNAME);

        self::where(['FID' => $this->FID])->update(['hx_group_id' => $returnData['data']['groupid']]);
    }

    /**
     * 获取剧组的所有部门长
     */
    public function leaders()
    {
        $distinctLeaderIds = $this->groups()->where('FLEADERID', '!=', '')
                                  ->whereNotNull('FLEADERID')
                                  ->selectRaw('distinct FLEADERID')
                                  ->lists('FLEADERID');

        return User::whereIn('FID', $distinctLeaderIds->all())->get();
    }

    /**
     * 一个剧组有多个消息
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function messages()
    {
        return $this->hasMany(Message::class, 'movie_id', 'FID');
    }

    /**
     * 一个剧组有很多备忘录,
     * 所谓的部门备忘录是使用部门成员的ids进行判断的
     */
    public function todos()
    {
        return $this->hasMany(Todo::class, 'movie_id', 'FID');
    }


    /**
     * 一个剧组有多个通告单
     */
    public function noticeMessages()
    {
        return $this->messages()->where('messages.type', 'NOTICE')
                    ->where('messages.is_delete', 0)
                    ->where('messages.is_undo', 0);
    }

    /**
     * 将用户加入剧组通告单的接受者里
     *
     * @param $userId
     *
     * @return bool
     */
    public function addUserToMessageReceiver($userId)
    {
        $unDeletedMessages = $this->messages()->where('messages.is_delete', 0)->where('messages.is_undo', 0)->get();

        if ($unDeletedMessages->count() == 0) {
            return true;
        }

        foreach ($unDeletedMessages as $noticeMsg) {
            $noticeMsg->addUserToReceivers($userId);
        }
    }

    /**
     * 把用户添加到所加入的部门的所有发布的备忘录
     *
     * @param $userId
     *
     * @return bool
     */
    public function addUserToDepartmentTodo($userId)
    {
        $joinedDepartment = User::find($userId)->firstGroupInMovie($this->FID);

        $departmentTodos = $joinedDepartment->shareTodos();

        if ($departmentTodos->count() == 0) {
            return true;
        }

        foreach ($departmentTodos as $todo) {
            $todo->addUserToReceivers($userId);
        }
    }

}


