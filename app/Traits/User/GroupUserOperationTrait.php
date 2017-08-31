<?php
namespace App\Traits\User;


use App\Models\GroupUser;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * ----------------------------------------
 * 请不要将该trait用于除了User之外的任何地方!!!
 * 该trait只用于分解用户有关艺人资料的操作
 * ----------------------------------------
 *
 * @package App\Traits\User
 */
trait GroupUserOperationTrait
{
    /**
     * 用户的所有组员身份
     *
     * @return HasMany;
     */
    public function groupUsers()
    {
        return $this->hasMany(GroupUser::class, 'FUSER', 'FID');
    }

    /**
     * 用户在该剧组的所有组员身份
     *
     * @param $movieId
     *
     * @return Collection
     */
    public function groupUsersInMovie($movieId)
    {
        return $this->hasMany(GroupUser::class, 'FUSER', 'FID')->where('FMOVIE', $movieId)->orderBy('t_biz_groupuser.FNEWDATE')->get();
    }

    /**
     * 用户在剧组的第一个组员身份
     *
     * @param $movieId
     *
     * @return mixed
     */
    public function firstGroupUserInMovie($movieId)
    {
        return $this->groupUsersInMovie($movieId)->first();
    }


}
