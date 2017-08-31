<?php

namespace App\Models;

use App\Traits\ModelFindTrait;
use App\User;
use DB;
use Illuminate\Database\Eloquent\Model;

/**
 * @property mixed FID
 * @property mixed FGROUPTYPE
 * @property mixed movie
 */
class Group extends Model
{
    use ModelFindTrait;

    const TYPE_ZHI_PIAN     = 30;  //制片部门
    const TYPE_TONG_CHOU    = 10;  //统筹部门
    const TYPE_DIRECTOR     = 40;  //导演部门
    const TYPE_OTHER        = 20;  //其他部门
    const TYPE_NANZHU_ASSIT = 50;  //南竹客服

    const UNIQUE_USER = true;

    //环信聊天群组的头像,暂时因为合成图片有点复杂,先用这个
    const DEPARTMENT_HX_GROUP_COVER_URL = 'http://nanzhu.oss-cn-shanghai.aliyuncs.com/banners/app_create_group_cover@2x.png';
    const APP_CREATE_HX_GROUP_COVER_URL = 'http://nanzhu.oss-cn-shanghai.aliyuncs.com/banners/department_group_cover@2x.png';

    public $timestamps = false;

    protected $table = 't_biz_group';

    protected $guarded = [];

    public $incrementing = false;

    /**
     * 一个部门属于一个剧组
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function movie()
    {
        return $this->belongsTo(Movie::class, 'FMOVIE', 'FID');
    }

    /**
     * 一个组可能有很多申请加入
     */
    public function applies()
    {
        return $this->hasMany(JoinGroup::class, 'group_id', 'FID');
    }

    /**
     * 一个组可能有多个组成员
     */
    public function members()
    {
        return $this->hasMany(GroupUser::class, 'FGROUP', 'FID');
    }

    /**
     * 一个部门有多个群公告
     */
    public function publicNotices()
    {
        return $this->hasMany(HxGroupPublicNotice::class, 'group_id', 'FID')
                    ->orderBy('hx_group_public_notices.created_at', 'desc');
    }

    /**
     * 没有审核的入组申请
     */
    public function unAuditedApplies()
    {
        return $this->hasMany(JoinGroup::class, 'group_id', 'FID')
                    ->where('join_group.audit_status', JoinGroup::STATUS_WAIT_AUDIT)
                    ->groupBy('user_id', 'group_id', 'movie_id')
                    ->get();
    }

    /**
     * 部门的部门长
     * @return string
     */
    public function leader()
    {
        $user = User::where("FID", $this->FLEADERID)->first();
        if ($user) {
            return $user->FNAME;
        }
        return "";
    }


    /**
     * 获取组内成员
     */
    public function usersInGroup()
    {
        return GroupUser::where(['t_biz_groupuser.FGROUP' => $this->FID])
                        ->join('t_sys_user', 't_sys_user.FID', '=', 't_biz_groupuser.FUSER')
                        ->selectRaw(
                            't_biz_groupuser.FID,
                 t_biz_groupuser.FUSER,
                 t_sys_user.FNAME as name,
                 t_sys_user.FPICURL as user_pic_url,
                 COALESCE(t_biz_groupuser.FOPEN, 20) as FOPEN,
                 t_sys_user.FSEX as sex,
                 t_biz_groupuser.FREMARK,
                 t_sys_user.FPHONE as phone_number'
                        )
                        ->get()
                        ->all();
    }


    /**
     * 获取部门部门长
     */
    public function leadUser()
    {
        $leader = User::find($this->FLEADERID);

        return empty($leader) ? null : $leader;
    }

    /**
     * 判断一个组是否可以删除
     * @return bool
     */
    public function canDelete()
    {
        return !$this->isEssential();
    }

    /**
     * 部门是否是必须的
     * @return bool
     */
    public function isEssential()
    {
        $groupsCanNotDelete = array_column(Movie::$essentialDepartments, 'type');

        return in_array($this->FGROUPTYPE, $groupsCanNotDelete);
    }

    /**
     * 一个组是否包含最高权限用户角色
     *
     * @return boolean
     */
    public function admin()
    {
        return GroupUser::where(['FGROUPUSERROLE' => 10, 'FGROUP' => $this->FID])->first();
    }

    /**
     * 把用户设置为改组组长
     *
     * @param $userId
     */
    public function setLeader($userId)
    {
        DB::table('t_biz_group')->where('FID', $this->FID)->update(['FLEADERID' => $userId]);
    }

    /**
     * 获取组内所有成员
     *
     * @param bool $unique
     */
    public function users($unique = true)
    {
        $userIdArray = GroupUser::where('FGROUP', $this->FID)->lists('FUSER');

        if ($unique) {
            $userIdArray = $userIdArray->unique();
        }

        return User::whereIn('FID', $userIdArray)->get();
    }

    /**
     * 创建部门的环信群组聊天群组
     *
     * @param User|integer $user
     */
    public function createChatGroupWithOwner($user)
    {
        if (!($user instanceof User)) {
            $user = User::find($user);
        }

        $chatGroupDesc = $this->FNAME . '/' . $this->movie->FNAME;

        $easeUser = new EaseUser();

        //创建部门群聊
        $hxGroupId = $easeUser->createDepartmentChatGroup($user->FID, $this->FID, $chatGroupDesc)['data']['groupid'];

        //发送群组
        $easeUser->sendUserCreateGroupMsg($user->FID, $hxGroupId);

        self::where(['FID' => $this->FID])->update(['hx_group_id' => $hxGroupId]);

        return self::find($this->FID);
    }

    /**
     * 创建群组聊天室
     * 用于显示指定群组群主的场合
     * 由于主键的问题,所以使用self::update之后不会直接把model obj 的attribute更改,所以只能返回一个新的model obj
     */
    public function createChatGroup()
    {
        $leader = $this->leadUser();

        $owner = $leader ?: $this->movie->admin();

        $owner = $owner ?: User::find(User::APP_ADMIN);

        return $this->createChatGroupWithOwner($owner);
    }


    /**
     * 获取环信群组名字
     */
    public function getHxGroupNameAttribute()
    {
        return Easemob::GROUP_PREFIX . $this->FID;
    }


    /**
     * 部门是否注册环信
     */
    public function hadRegisterHx()
    {
        return !empty($this->hx_group_id);
    }

    /**
     * 部门没有注册环信
     */
    public function hadNotResigerHx()
    {
        return !$this->hadRegisterHx();
    }


    /**
     * 获取群组成员
     *
     * @return array
     */
    public function getHxMembers()
    {
        if ($this->hadNotResigerHx()) {
            return [];
        }

        $easeUser = new EaseUser();

        $membersData = $easeUser->groupMembers($this->hx_group_id)['data'];

        return array_map(function ($member) use ($easeUser) {
            $ownerName = isset($member['owner']) ? $member['owner'] : $member['member'];

            return EaseUser::getUserFromName($ownerName);
        }, $membersData);
    }

    /**
     * 获取环信群组名字
     */
    public function getHxTitleAttribute()
    {
        if ($this->hx_group_title) {
            return $this->hx_group_title;
        }

        return $this->FNAME . '/' . $this->movie->FNAME;
    }

    /**
     * 部门所有的备忘录
     * ---------------------------
     * 查询所有创建者是这个部门的备忘录
     */
    public function todos()
    {
        $memberUserIdArray = $this->members()->selectRaw('distinct FUSER')->lists('FUSER')->all();

        return Todo::whereIn('user_id',$memberUserIdArray)->get();
    }

    /**
     * 部门里所有分享的备忘录
     */
    public function shareTodos()
    {
        $memberUserIdArray = $this->members()->selectRaw('distinct FUSER')->lists('FUSER')->all();

        return Todo::whereIn('user_id',$memberUserIdArray)->share()->get();
    }


}
