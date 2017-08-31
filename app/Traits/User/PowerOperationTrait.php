<?php
namespace App\Traits\User;

use App\Models\ContactPower;
use App\Models\DailyReportPower;
use App\Models\ProgressPower;
use App\Models\ReceivePower;

trait PowerOperationTrait
{
    /**
     * 用户是否被赋予剧组的剧组通讯录查看权限
     *
     * @param $movieId
     *
     * @return bool
     */
    public function hadAssignedContactPowerInMovie($movieId)
    {
        $allGroupUserIdArray = $this->groupUsersInMovie($movieId)->lists('FID');

        return ContactPower::where('FMOVIEID', $movieId)->whereIn('FGROUPUSERID', $allGroupUserIdArray)->count() > 0;
    }


    /**
     * 用户是否被赋予剧组的拍摄进度查看权限
     *
     * @param $movieId
     *
     * @return bool
     */
    public function hadAssignedProgressPowerInMovie($movieId)
    {
        $allGroupUserIdArray = $this->groupUsersInMovie($movieId)->lists('FID');

        return ProgressPower::where('FMOVIEID', $movieId)->whereIn('FGROUPUSERID', $allGroupUserIdArray)->count() > 0;
    }


    /**
     * 用户是否被赋予剧组的接受详情录查看权限
     *
     * @param  int $movieId
     *
     * @return bool
     */
    public function hadAssignedReceivePowerInMovie($movieId)
    {
        $allGroupUserIdArray = $this->groupUsersInMovie($movieId)->lists('FID');

        return ReceivePower::where('FMOVIEID', $movieId)->whereIn('FGROUPUSERID', $allGroupUserIdArray)->count() > 0;
    }

    /**
     * 赋予用户剧组的通讯录查看权限
     *
     * @param $movieId
     */
    public function assignContactPowerInMovie($movieId)
    {
        $groupUsers = $this->groupUsersInMovie($movieId);

        foreach ($groupUsers as $groupUser) {
            ContactPower::create(['FMOVIEID' => $movieId, 'FGROUPUSERID' => $groupUser->FID,'FID'=>ContactPower::max('FID')+1]);
        }
    }

    /**
     * 赋予用户拍摄进度查看权限
     *
     * @param $movieId
     */
    public function assignProgressPowerInMovie($movieId)
    {
        $groupUsers = $this->groupUsersInMovie($movieId);

        foreach ($groupUsers as $groupUser) {
            ProgressPower::create(['FMOVIEID' => $movieId, 'FGROUPUSERID' => $groupUser->FID,'FID'=>ProgressPower::max('FID')+1]);
        }
    }

    /**
     * 赋予用户接受详情权限
     *
     * @param $movieId
     */
    public function assignReceivePowerInMovie($movieId)
    {
        $groupUsers = $this->groupUsersInMovie($movieId);

        foreach ($groupUsers as $groupUser) {
            ReceivePower::create(['FMOVIEID' => $movieId, 'FGROUPUSERID' => $groupUser->FID,'FID'=>ReceivePower::max('FID')+1]);
        }
    }

    public function hadAssignedDailyReportPower($movieId)
    {
        $allGroupUserIdArray = $this->groupUsersInMovie($movieId)->lists('FID');

        return DailyReportPower::where('movie_id', $movieId)->whereIn('group_user_id', $allGroupUserIdArray)->count() > 0;
    }

}
