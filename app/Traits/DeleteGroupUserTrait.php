<?php

namespace App\Traits;

/**
 * 用于在一个Group中删除一个groupuser的时候,
 * 各个相关model中删除groupuser
 *
 * Class DeleteGroupUserTrait
 */
trait  DeleteGroupUserTrait
{

    /**
     * 删除某一组成员
     *
     * @param $groupUserId
     */
    public static function removeGroupUser($groupUserId)
    {
        $crewNotificationGroupUser = static::where('FGROUPUSERID', $groupUserId)->first();

        if ($crewNotificationGroupUser) {
            $crewNotificationGroupUser->delete();
        }
    }

}