<?php
namespace App\Traits\User;

use App\Models\GroupUser;
use App\Models\Movie;
use Carbon\Carbon;

/**
 * ----------------------------------------
 * 请不要将该trait用于除了User之外的任何地方!!!
 * 该trait只用于分解用户有关电影的操作
 * ----------------------------------------
 *
 * @package App\Traits\User
 */
trait MovieOperationTrait
{
    /**
     * 用户是否在某剧组
     *
     * @param $movieId
     *
     * @return bool
     */
    public function isInMovie($movieId)
    {
        return GroupUser::where(['FMOVIE' => $movieId, 'FUSER' => $this->FID])->count() > 0;
    }

    /**
     * 用户是否不在某剧组
     *
     * @param $movieId
     *
     * @return bool
     */
    private function notInMovie($movieId)
    {
        return !$this->isInMovie($movieId);
    }

    /**
     * 用户是否剧组最高权限者
     *
     * @param $movieId
     *
     * @return bool
     */
    public function isAdminOfMovie($movieId)
    {
        if ($this->notInMovie($movieId)) {
            return false;
        }

        return GroupUser::where([
            'FMOVIE' => $movieId,
            'FUSER'  => $this->FID
        ])->first()->FGROUPUSERROLE == GroupUser::ROLE_ADMIN;
    }

    /**
     * 不是剧组最高权限
     * @param $movieId
     *
     * @return bool
     */
    public function isNotAdminOfMovie($movieId)
    {
        return ! $this->isAdminOfMovie($movieId);
    }


    /**
     * 退出剧组
     *
     * @param $movieId
     */
    public function exitMovie($movieId)
    {
        $groupUsers = $this->groupUsersInMovie($movieId);

        foreach ($groupUsers as $groupUser) {
            $groupUser->exitGroup();
        }
    }


    /**
     * 判断用户在剧组中是否统筹组
     *
     * @param $movieId
     *
     * @return bool
     */
    public function isTongChouInMovie($movieId)
    {
        return GroupUser::is_tongchou($movieId, $this->FID);
    }

    /**
     * 判断用户在剧组中是否制片组
     *
     * @param  $movieId
     *
     * @return bool
     */
    public function isZhiPianInMovie($movieId)
    {
        return GroupUser::is_zhipian($movieId, $this->FID);
    }


    /**
     * 判断用户在剧组中是否导演组
     *
     * @param  $movieId
     *
     * @return bool
     */
    public function isDirectorInMovie($movieId)
    {
        return GroupUser::is_director($movieId, $this->FID);
    }

    /**
     * 获取在剧组中的职位
     *
     * @param $movieId
     *
     * @return string
     */
    public function positionInMovie($movieId)
    {
        $firstGroupUser = $this->groupUsersInMovie($movieId)->first();

        if(!$firstGroupUser){
            return '用户已经退出剧组';
        }

        return $firstGroupUser->FREMARK ? $firstGroupUser->FREMARK : '暂未填写职位';
    }

    /**
     * 用户加入剧组的时间
     *
     * @param $movieId
     *
     * @return Carbon
     */
    public function joinMovieTime($movieId)
    {
        return  Carbon::createFromTimestamp(strtotime($this->firstGroupUserInMovie($movieId)->FNEWDATE));
    }

    /**
     * 用户是否是剧组的部门长(不管多部门,只要有一个是就行)
     * @param $movieId
     *
     * @return bool
     */
    public function isLeaderInMovie($movieId)
    {
        return in_array($this->FID,Movie::find($movieId)->leaders()->lists('FID')->all());
    }

    /**
     * 获取用户所在的所有剧
     * @param $FID
     *
     * @return
     */
    public function joinedMovies()
    {
          $movieId = GroupUser::where('FUSER',$this->FID)->lists('FMOVIE')->all();
          return Movie::whereIn('FID',$movieId)->get();
    }
}
