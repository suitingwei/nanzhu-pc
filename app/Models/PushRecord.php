<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class PushRecord extends Model
{
    //

    public static function send($aliyuntokens, $title, $body, $summary, $extra, $is_all)
    {

        //$aliyuntokens = '10a0c089525943fbb5ab148b8848a7e5,ff742248bb664e94bcff5893c7e8f7b5';
        //$aliyuntokens = '10a0c089525943fbb5ab148b8848a7e5';
        //$title = "test title";
        //$body = "test body";
        //$summary = "test summary";
        //$extra = ["uri" => "nanzhu://message?id=64"];
        //dispatch(new SendPush($aliyuntokens,$title,$body,$summary,json_encode($extra)));
        $status               = Pusher::send($aliyuntokens, $title, $body, $summary, json_encode($extra), $is_all);
        $record               = new PushRecord;
        $record->aliyuntokens = $aliyuntokens;
        $record->title        = $title;
        $record->body         = $body;
        $record->summary      = $summary;
        $record->extra        = json_encode($extra);
        $record->status       = $status->ResponseId;
        $record->save();

    }

    /**
     * 对多个用户发送推送
     *
     * @param array $userIds
     * @param $title
     * @param $body
     * @param $extra
     * @param $isAll
     */
    public static function sendManyByUserIds($userIds,$title,$body,$extra,$isAll)
    {
        foreach ($userIds as $notifyPersonId) {
            $user = User::find($notifyPersonId);

            if ($user && $user->FALIYUNTOKEN) {
                self::send($user->FALIYUNTOKEN, '南竹通告单', $title, $body, $extra, $isAll);
            }
        }
    }
}
