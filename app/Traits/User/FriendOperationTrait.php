<?php

namespace App\Traits\User;


use App\Exceptions\FriendException;
use App\Models\Friend;
use App\Models\FriendApplication;
use App\User;
use Carbon\Carbon;
use Exception;

trait FriendOperationTrait
{
    /**
     * 一个人有多个好友
     */
    public function friends()
    {
        return $this->hasMany(Friend::class, 'user_id', 'FID');
    }

    /**
     * 获取用户的
     */
    public function friendUsers()
    {
        return User::whereIn('FID', $this->friends()->lists('friend_id')->all())->get();
    }

    /**
     * 向用户发起好友申请
     *
     * @param User|integer $friend
     * @param              $content
     *
     * @return bool
     * @throws Exception
     */
    public function applyUserBeFriend($friend, $content)
    {
        if (!($friend instanceof User)) {
            $friend = User::find($friend);
        }

        //已经是好友的不能发起申请
        if ($this->isFriendOfUser($friend)) {
            throw new FriendException('已经加为好友,不能重复添加', FriendException::USER_HAD_BEEN_FRIEND);
        }

        //有还没有被处理的好友申请
        if ($this->haveUnApprovedApplications($friend)) {
            throw new FriendException('你已经申请过添加好友,请耐心等待反馈', FriendException::FRIEND_APPLICATION_APPROVING);
        }

        FriendApplication::create([
            'applier_id'  => $this->FID,
            'content'     => $content,
            'receiver_id' => $friend->FID,
            'is_approved' => false,
        ]);

        return true;
    }

    /**
     * 用户收到的好友申请
     * (注意此处获取的好友申请可能有重复的,比如一个人申请好友成功之后又删除,再申请)
     */
    public function receivedApplications()
    {
        return $this->hasMany(FriendApplication::class, 'receiver_id', 'FID')->orderBy('created_at', 'desc');
    }

    /**
     * 用户发起的好友申请
     */
    public function createdApplications()
    {
        return $this->hasMany(FriendApplication::class, 'applier_id', 'FID')->orderBy('created_at', 'desc');
    }

    /**
     * 是否某一个用户的好友
     *
     * @param User|integer $friend
     *
     * @return bool
     */
    public function isFriendOfUser($friend)
    {
        if (!($friend instanceof User)) {
            $friend = User::find($friend);
        }

        return $this->friends()->where('friend_id', $friend->FID)->count() > 0;
    }

    /**
     * 用户是否某人好友
     *
     * @param User $user
     *
     * @return bool
     */
    public function isNotFriendOfUser(User $user)
    {
        return !$this->isFriendOfUser($user);
    }

    /**
     * 是否申请过加好友
     *
     * @param User|integer $user
     *
     * @return bool
     */
    public function hadAppliedUserBeFriend($user)
    {
        if (!($user instanceof User)) {
            $user = User::find($user);
        }

        return $this->createdApplications()->where('receiver_id', $user->FID)->count() > 0;
    }

    /**
     * 是否申请过加好友
     *
     * @param User|integer $user
     *
     * @return bool
     */
    public function hadNotAppliedUserBeFriend($user)
    {
        return !$this->hadAppliedUserBeFriend($user);
    }

    /**
     * 对某一位用户的好友申请是否还有未处理的
     *
     * @param User|integer $user
     *
     * @return bool
     */
    public function haveUnApprovedApplications($user)
    {
        if ($this->hadNotAppliedUserBeFriend($user)) {
            return false;
        }

        if (!($user instanceof User)) {
            $user = User::find($user);
        }

        return $this->createdApplications()->where('receiver_id', $user->FID)->orderBy('created_at',
            'desc')->first()->is_approved == FriendApplication::NOT_APPROVED;
    }


    /**
     * 同意好友申请
     *
     * @param FriendApplication|integer $application
     *
     * @return bool
     * @throws Exception
     */
    public function approveFriendApplication($application)
    {
        if (!($application instanceof FriendApplication)) {
            $application = FriendApplication::find($application);
        }

        //如果不是给我的好友申请不能同意
        if ($application->receiver_id != $this->FID) {
            throw new FriendException('不能同意别人的好友申请');
        }

        if ($application->is_approved) {
            throw new FriendException('申请已经同意,不能重复操作');
        }

        $application->update(['is_approved' => true, 'approved_at' => Carbon::now()]);

        return true;
    }

    /**
     * 从A的好友列表删除B
     * --------------------
     * 1.删除A,B之间相互的好友关系
     * 2.从A的环信好友列表删除B
     * 3.删除A向B发出的好友申请
     * 4.删
     *
     * @param $friendId
     */
    public function deleteFriend($friendId)
    {
        $friendShips = Friend::where([
            'user_id'   => $this->FID,
            'friend_id' => $friendId
        ])->orWhere([
            'friend_id' => $this->FID,
            'user_id'   => $friendId
        ])->get();

        foreach ($friendShips as $friendShip) {
            $user = User::find($friendShip->user_id);

            //删除环信好友
            $user->deleteHxFriend($friendShip->friend_id);

            //删除用户A向B发出的好友申请
            $user->createdApplications()->where('receiver_id',$friendId)->delete();

            $friendShip->delete();
        }
    }


}