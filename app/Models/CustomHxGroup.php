<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomHxGroup extends Model
{
    const TYPE_CUSTOM = 'CUSTOM';
    const TYPE_DEPARTMENT ='DEPARTMENT';

    const DEFAULT_GROUP_COVER_URL = Group::APP_CREATE_HX_GROUP_COVER_URL;

    public $guarded =[];

    public function publicNotices()
    {
        return $this->hasMany(HxGroupPublicNotice::class,'hx_group_id','hx_group_id');
    }

    /**
     * 获取环信群组名字
     */
    public function getHxTitleAttribute()
    {
        return $this->title;
    }


    /**
     * 获取群组成员
     *
     * @return array
     */
    public function getHxMembers()
    {
        $easeUser = new EaseUser();

        $membersData = $easeUser->groupMembers($this->hx_group_id)['data'];

        return array_map(function ($member) use ($easeUser) {
            $ownerName = isset($member['owner']) ? $member['owner'] : $member['member'];

            return EaseUser::getUserFromName($ownerName);
        }, $membersData);
    }
}
