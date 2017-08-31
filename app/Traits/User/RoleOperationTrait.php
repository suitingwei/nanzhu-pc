<?php

namespace App\Traits\User;

use App\Models\Group;
use App\Models\Role;

/**
 * ----------------------------------------
 * 请不要将该trait用于除了User之外的任何地方!!!
 * 该trait只用于分解用户有关电影的操作
 * ----------------------------------------
 *
 * @package App\Traits\User
 */
trait RoleOperationTrait
{
    /**
     * @return mixed
     */
    public function roles()
    {
        return Role::leftJoin('role_user', 'roles.id', '=', 'role_user.role_id')->where("role_user.user_id", $this->FID)->get();
    }

    /**
     * @param $role_id
     *
     * @return bool
     */
    public function has_role($role_id)
    {
        $role = Role::leftJoin('role_user', 'roles.id', '=', 'role_user.role_id')->where("role_user.user_id", $this->FID)->where("role_user.role_id", $role_id)->first();
        if ($role) {
            return true;
        }
        return false;
    }


    /**
     * @return string
     */
    public function sex_desc()
    {
        if ($this->FSEX == 10) {
            return "男";
        }
        if ($this->FSEX == 20) {
            return "女";
        }
    }


    /**
     * 判断用户是不是统筹,不管哪一个剧组
     */
    public function isTongchou()
    {
        return $this->isCertainGroup('统筹');
    }

    /**
     * 判断用户是制片组De
     * @return bool
     */
    public function isZhiPian()
    {
        return $this->isCertainGroup('制片');
    }


    /**
     * 判断用户是导演组的
     * @return bool
     */
    public function isDirector()
    {
        return $this->isCertainGroup('导演');
    }

    /**
     * 判断用户是某一个部门的
     * @param $groupName
     *
     * @return bool
     */
    private  function isCertainGroup($groupName)
    {
        return Group::whereIn('FID',$this->groupUsers()->selectRaw('distinct FGROUP')->lists('FGROUP')->all())
                    ->where('FNAME','like','%'.$groupName.'%')
                    ->count() > 0;
    }

}
