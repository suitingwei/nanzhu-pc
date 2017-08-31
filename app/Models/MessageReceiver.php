<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

/**
 * @property  int receiver_id
 * @property  int message_id
 * @property int  is_read
 */
class MessageReceiver extends Model
{
    //
    protected $fillable = ["receiver_id", "message_id", "is_read"];

    /**
     * 一个messagereceiver属于一个用户
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'receiver_id', 'FID');
    }

    public static function is_read($notice_id, $excel_id, $user_id)
    {
        $message = Message::where("notice_id", $notice_id)->where("notice_file_id", $excel_id)->orderby("id",
            "desc")->first();
        if ($message) {
            $ms = MessageReceiver::where("message_id", $message->id)->where("receiver_id", $user_id)->first();
            if ($ms && $ms->is_read == 1) {
                return true;
            }
        }
        return false;
    }

    public static function read_rate($notice_id, $excel_id)
    {
        $message = Message::where("notice_id", $notice_id)->where("notice_file_id", $excel_id)->orderby("id",
            "desc")->first();
        \Log::info($message->id);
        if ($message) {
            $total = MessageReceiver::where("message_id", $message->id)->count();
            $read  = MessageReceiver::where("message_id", $message->id)->where("is_read", 1)->count();
            return $read . "/" . $total;
        }

        return 0;
    }

    /**
     *
     * @return boolean
     */
    public function hadRead()
    {
        return $this->is_read == 1;
    }

    /**
     * @return bool
     */
    public function hadNotRead()
    {
        return !$this->hadRead();
    }


}
