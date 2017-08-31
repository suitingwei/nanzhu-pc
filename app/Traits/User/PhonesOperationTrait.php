<?php
namespace App\Traits\User;


use App\Models\Group;
use App\Models\GroupUser;
use App\Models\SparePhone;
use DB;
use Illuminate\Database\Eloquent\Collection;

trait PhonesOperationTrait
{
    /**
     * 获取用户在这个剧组的共享电话
     * 由于现在一个用户可能有多个组员身份,但是只有一套共享电话
     * 公开电话保存在第一个groupUser身上
     *
     * @param $movieId
     *
     * @return Collection
     */
    public function sharePhonesInMovie($movieId)
    {
        $firstGroupUser = $this->groupUsersInMovie($movieId)->first();

        return $firstGroupUser->sharePhones()->checked()->get();
    }

    /**
     * 用户是否在这个剧组中公开电话
     *
     * @param $movieId
     *
     * @return bool
     */
    public function isSharePhonesInMovieOpened($movieId)
    {
        $firstGroupUser = $this->groupUsersInMovie($movieId)->first();

        if(!$firstGroupUser){
            return false;
        }
        return $firstGroupUser->FOPENED ==  GroupUser::PHONE_OPENED;
    }


    /**
     * 把用户在这个剧组的电话设为私有
     * @param $movieId
     */
    public function setPhonePrivateInMovie($movieId)
    {
        foreach ($this->groupUsersInMovie($movieId) as $groupUser) {
            $groupUser->setPhonePrivate();
        }
    }

    /**
     * 把用户在这个剧组的电话设为公开
     *
     * @param $movieId
     */
    public function setPhonePublicInMovie($movieId)
    {
        foreach ($this->groupUsersInMovie($movieId) as $groupUser) {
            $groupUser->setPhonePublic();
        }
    }

    /**
     * 是否加入某个剧组的公开电话
     *
     * @param $movieId
     *
     * @return bool
     */
    public function hadJoinedPublicContactsInMovie($movieId)
    {
        foreach ($this->groupUsersInMovie($movieId) as $groupUser) {
            if($groupUser->hadJoinedPublicContacts()){
                return true;
            }
        }
        return false;
    }

    /**
     * 用户添加分享电话
     *
     * @param GroupUser $groupUser
     * @param           $phoneChecked
     */
    public function createSharePhones(GroupUser $groupUser,$phoneChecked=SparePhone::CHECKED_ON)
    {
        SparePhone::create([
            'FGROUPUSERID' => $groupUser->FID,
            'FPHONE'       => $this->FPHONE,
            'FChecked'     => $phoneChecked,
            'FREGPHONE'    => SparePhone::PHONE_REGISTER,
            'FID'          => SparePhone::max('FID') + 1,
            'FPOS'         => 1,
            'FNEWDATE'     => date('Y-m-d H:i:s', time()),
            'FEDITDATE'    => date('Y-m-d H:i:s', time())
        ]);

        for ($i = 2; $i <= 3; $i++) {
            SparePhone::createNormalPhone($groupUser, $i);
        }

    }

}