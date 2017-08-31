<?php

namespace App\Traits\User;

use App\Models\Profile;
use App\Models\ProfileShare;

/**
 * ----------------------------------------
 * 请不要将该trait用于除了User之外的任何地方!!!
 * 该trait只用于分解用户有关艺人资料的操作
 * ----------------------------------------
 *
 * @package App\Traits\User
 */
trait ProfileOperationTrait{

    /**
     * 一个人有一份个人资料
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne;
     */
    public function profile()
    {
        return $this->hasOne(Profile::class, 'user_id', 'FID');
    }

    /**
     * 一个人可以有多个协助编辑的艺人资料
     */
    public function profileShares()
    {
        return $this->hasMany(ProfileShare::class,'user_id','FID');
    }

    /**
     * 可以协助编辑的艺人资料
     */
    public function canEditProfiles()
    {
        $profileIdArray =  $this->profileShares()->lists('profile_id')->all();

        return Profile::whereIn('id',$profileIdArray)->get();
    }

}
