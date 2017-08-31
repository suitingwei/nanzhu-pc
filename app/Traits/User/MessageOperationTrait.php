<?php
namespace App\Traits\User;

use App\Models\Message;
use App\Models\Notice;

trait MessageOperationTrait
{

    /**
     * 用户收到的通知
     *
     * @param $type
     *
     * @return mixed
     */
    public function receivedMessages($type = null)
    {
        $messages = Message::sendToUserWithType($this->FID,$type);

        return $messages->get();
    }

    /**
     * 用户收到的通告单消息
     */
    public function receivedNoticeMessages()
    {
        return $this->receivedMessages(Message::TYPE_NOTICE);
    }


}
