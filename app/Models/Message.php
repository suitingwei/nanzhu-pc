<?php

namespace App\Models;

use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;

class Message extends Model
{
    const TYPE_SYSTEM             = 'SYSTEM';
    const TYPE_BLOG               = 'BLOG';
    const TYPE_JUZU               = 'JUZU';
    const TYPE_NOTICE             = 'NOTICE';
    const TYPE_CHAT_GROUP         = 'CHATGROUP';  //聊天类型,这个用户前端消息界面显示,并不是message这个表里的
    const TYPE_FRIEND_APPLICATION = 'FRIEND_APPLICATION'; //好友申请类型,这个用户前端消息界面显示,并不是message这个表里的
    const TYPE_FRIEND             = 'FRIEND'; //好友类型

    const SCOPE_ALL       = 0;
    const SCOPE_SOME_BODY = 1;

    const MESSAGE_HAD_READ = 1;
    const MESSAGE_NOT_READ = 0;

    const HAD_UNDO = 1; //已经撤销
    const NOT_UNDO = 0; //没有撤销

    public static $feiyeUploadFileAllowedTypes = [
        'jpg',
        'png',
        'jpeg',
        'doc',
        'xlsx',
        'pdf',
        'xls',
        'docx',
        'ppt',
        'pptx'
    ];
    public static $feiyeUploadFileImageTypes = ['jpg', 'png', 'jpeg'];

    /**
     * 所有扉页允许上传的文件类型
     *
     * @param bool $withDot
     *
     * @return array
     */
    public static function feiyeUploadFileAllowedTypes($withDot = false)
    {
        if (!$withDot) {
            return static::$feiyeUploadFileAllowedTypes;
        }

        return array_map(function ($value) {
            return '.' . $value;
        }, static::$feiyeUploadFileAllowedTypes);

    }

    /**
     * 所有扉页允许上传的文件,不包含图片类型
     *
     * @param bool $withDot
     *
     * @return array
     */
    public static function feiyeUploadFileAllowedTypesWithoutImage($withDot = false)
    {
        $filteredTypes = array_diff(static::$feiyeUploadFileAllowedTypes, static::$feiyeUploadFileImageTypes);

        if (!$withDot) {
            return $filteredTypes;
        }

        return array_map(function ($value) {
            return '.' . $value;
        }, $filteredTypes);
    }

    protected $fillable = [
        "notice_type",
        "notice_id",
        "notice_file_id",
        "filename",
        "movie_id",
        "scope",
        "notice",
        "scope_ids",
        "type",
        "uri",
        "title",
        "content",
        "from",
        "to_user",
        "is_undo",
        "is_delete"
    ];

    /**
     * 所有发送消息的类型,并不包含为了用于前端显示而添加的好友申请和环信聊天类型
     */
    public static function allMessageTypes()
    {
        return [
            self::TYPE_BLOG,
            self::TYPE_JUZU,
            self::TYPE_NOTICE,
            self::TYPE_SYSTEM,
        ];
    }

    public static function types()
    {
        return ["SYSTEM" => "系统消息", "BLOG" => "扉页消息", "JUZU" => "剧组通知", "NOTICE" => "通告单"];
    }

    public static function is_undo($notice_id, $notice_file_id)
    {
        $message = Message::where("notice_id", $notice_id)->where("notice_file_id", $notice_file_id)->orderby("id",
            "desc")->first();

        if ($message && $message->is_undo == 1) {
            return true;
        }
        return false;
    }

    /**
     * 安卓的剧组通知,剧本扉页的上传图片
     * 前段上传完oss,直接post图片url
     *
     * @param $files
     * @param $message
     */
    private static function uploadMultipleImages($files, $message)
    {
        if (count($files) == 0) {
            return;
        }
        foreach ($files as $key => $file) {
            if ($file) {
                $picture             = new Picture;
                $picture->url        = $file;
                $picture->message_id = $message->id;
                $picture->save();
            }
        }
    }


    /**
     * ios上传图片,直接上传file
     *
     * @param $files
     * @param $message
     *
     */
    private static function uploadIosImages($files, $message)
    {
        foreach ($files as $key => $file) {
            if ($file) {
                $picture             = new Picture;
                $picture->url        = Picture::upload("pictures/" . $message->id, $file);
                $picture->message_id = $message->id;
                $picture->save();
            }
        }
    }

    /**
     * 获取推送通知的title,用于前端在app中显示
     *
     * @param $type
     *
     * @return string
     */
    private static function getMessageTitle($type)
    {
        $title = '';
        if (strtolower($type) == 'juzu') {
            $title = '剧组通知';
        } elseif (strtolower($type) == 'blog') {
            $title = '剧本扉页';
        }
        return $title;
    }

    /**
     * 上传剧组通知和剧本扉页的文件,类似通告单
     * 截止到2016-12-12只支持剧本扉页上传文件
     *
     * @param $request
     * @param $message
     */
    private static function uploadFiles(Request $request, $message)
    {
        if (!($fileUrls = $request->input('file_url'))) {
            return;
        }

        foreach ($fileUrls as $fileUrl) {
            $fileInfoJson = json_decode($fileUrl);

            MessageFiles::create([
                'message_id' => $message->id,
                'type'       => MessageFiles::TYPE_FEIYE,
                'file_url'   => $fileInfoJson->file_url,
                'file_type'  => $fileInfoJson->file_type,
                'file_name'  => $fileInfoJson->file_name
            ]);

        }

    }

    public function toArray()
    {
        $array["id"]       = $this->id;
        $array["type"]     = $this->type;
        $array["title"]    = $this->title;
        $array["content"]  = $this->content;
        $array["scope"]    = $this->scope;
        $array["notice"]   = $this->notice;
        $array["filename"] = $this->filename;
        if ($this->type == "SYSTEM") {
            $array["filename"] = $this->title;
        }
        $array["uri"] = $this->uri;
        $carbon       = Carbon::createFromTimestamp(strtotime($this->created_at));
        $carbon->setLocale("zh");
        $array["created_at"] = $carbon->diffForHumans();
        $array["d"]          = $carbon->toDateString();
        $array["pictures"]   = $this->pictures();
        return $array;
    }

    public function pictures()
    {
        $arr      = [];
        $pictures = Picture::where("message_id", $this->id)->get();

        $count = Picture::where("message_id", $this->id)->count();
        if ($count > 0) {
            foreach ($pictures as $picture) {
                $arr[] = $picture->url;
            }
        }
        return $arr;
    }

    /**
     * 创建新的剧组通知
     *
     * @param Request $request
     * @param         $user_id
     *
     * @return static
     */
    public static function buildMessageData(Request $request, $user_id)
    {
        $data = $request->except("pic_url");

        if ($data['type'] == "blog") {
            $data['type'] = "BLOG";
        }

        if ($data['type'] == "juzu") {
            $data['type'] = "JUZU";
        }

        $data['from']      = $user_id;
        $data['scope']     = 1;
        $data["scope_ids"] = implode(',', GroupUser::where("FMOVIE", $data['movie_id'])
                                                   ->selectRaw('distinct FUSER')
                                                   ->lists('FUSER')->all());
        $movie             = Movie::where("FID", $data['movie_id'])->first();
        if ($movie) {
            $data['title'] = $movie->FNAME . ":" . $data['title'];
        }

        $message = self::create($data);

        $title = urlencode(self::getMessageTitle($request->input('type')));

        $message->uri = env('APP_URL') . "/mobile/messages/{$message->id}?title={$title}";

        $message->save();

        return $message;
    }

    /**
     * @param Request $request
     * @param         $message
     */
    public static function uploadImages(Request $request, $message)
    {
        self::uploadMultipleImages($request->input('img_url'), $message);
    }

    /**
     * 创建新的剧组通知
     *
     * @param Request $request
     * @param         $userId
     *
     * @return Message
     */
    public static function createNewJuzuNofity(Request $request, $userId)
    {
        //创建剧组通知,剧本扉页
        $message = static::buildMessageData($request, $userId);

        //上传剧组通知,剧本扉页的图片
        static::uploadImages($request, $message);

        //上传剧本扉页关联的文件
        static::uploadFiles($request, $message);

        //创建消息接受者
        $message->createMessageReceives();

        return $message;
    }


    /**
     * 创建接受推送
     */
    public function createMessageReceives()
    {
        $extra = ["uri" => $this->uri];
        foreach (explode(",", $this->scope_ids) as $user_id) {
            if ($user_id) {
                $receiver              = new MessageReceiver;
                $receiver->receiver_id = $user_id;
                $receiver->message_id  = $this->id;
                $receiver->is_read     = 0;
                $receiver->save();
                $user = User::where("FID", $user_id)->first();
                if ($user) {
                    if ($user->FALIYUNTOKEN) {
                        PushRecord::send(
                            $user->FALIYUNTOKEN,
                            '南竹通告单',
                            $this->title,
                            $this->title,
                            $extra,
                            false);
                    }
                }
            }
        }
    }


    /**
     * 创建有人购买专业版视频的推送消息
     *
     * @param $shootOrder
     *
     * @param $needToNotifyPerson
     *
     * @return static
     */
    public static function createProfessionalVideoPurchasedNofify($shootOrder, $needToNotifyPerson)
    {
        $order_no = strtotime($shootOrder->created_at) . $shootOrder->id;

        $message = self::create([
            'from'      => 0,
            'type'      => self::TYPE_SYSTEM,
            'content'   => "订单号为:{$order_no},下单用户名:{$shootOrder->contact},联系电话{$shootOrder->phone},客户希望录制时间为:{$shootOrder->start_date},请尽快与其联系",
            'title'     => '南竹通告单有新的订单',
            'scope'     => 1,
            'notice'    => '',
            'scope_ids' => implode(',', $needToNotifyPerson),
        ]);

        $message->update(['uri' => env('APP_URL') . "/mobile/messages/{$message->id}"]);

        //创建消息接受接受
        foreach ($needToNotifyPerson as $userId) {
            MessageReceiver::create([
                'receiver_id' => $userId,
                'message_id'  => $message->id,
                'is_read'     => 0
            ]);
        }
        return $message;
    }

    /**
     * 将某人从接受名单中去除
     *
     * @param $userId
     */
    public function removeUserFromReceivers($userId)
    {
        $scopeIdArray = explode(',', $this->scope_ids);

        if (!in_array($userId, $scopeIdArray)) {
            return;
        }

        $scopeIdArray = array_filter($scopeIdArray, function ($scopeId) use ($userId) {
            return $scopeId != $userId;
        });

        $this->update(['scope_ids' => implode(',', $scopeIdArray)]);


        $receivers = $this->receivers()->where('receiver_id', $userId)->get();

        foreach ($receivers as $receiver) {
            $receiver->delete();
        }
    }

    /**
     * 把用户加入消息的接受者
     *
     * @param $userId
     */
    public function addUserToReceivers($userId)
    {
        $scopeIdArray = explode(',', $this->scope_ids);

        if (in_array($userId, $scopeIdArray)) {
            return;
        }

        array_push($scopeIdArray, $userId);

        $this->update(['scope_ids' => implode(',', $scopeIdArray)]);

        MessageReceiver::create([
            'receiver_id' => $userId,
            'message_id'  => $this->id,
            'is_read'     => 0
        ]);

    }

    /**
     * 消息通知的接受者们
     * @return HasMany
     */
    public function receivers()
    {
        return $this->hasMany(MessageReceiver::class, 'message_id', 'id');
    }

    /**
     * 所有已读的接受者们
     * @return mixed
     */
    public function hadReadReceivers()
    {
        return $this->receivers()->where('message_receivers.is_read', 1);
    }

    /**
     * 查询向全体用户发送的通知
     *
     * @param $query
     *
     * @return
     */
    public function scopeToAll($query)
    {
        return $query->where('scope', self::SCOPE_ALL);
    }

    /**
     * 查询发给某一个用用户的
     *
     * @param $query
     * @param $userId
     * @param $type
     */
    public function scopeSendToUserWithType($query, $userId, $type)
    {
        if ($type) {
            $query->where('type', $type);
        }

        return $query->where(function ($query) use ($userId, $type) {
            $query->where('scope_ids', 'like', "%{$userId}%");

            if ($type && $type == self::TYPE_SYSTEM) {
                $query->orWhere('scope', 0);
            }

        });
    }

    /**
     * 通告单类型
     * 每日/预备
     *
     * @param $query
     * @param $type
     *
     * @return
     */
    public function scopeNoticeType($query, $type)
    {
        return $query->where('notice_type', $type);
    }

    /**
     * 消息的阅读率
     */
    public function readRate()
    {
        return $this->hadReadReceivers()->count() . '/' . $this->receivers()->count();
    }


    /**
     * 发送push消息
     *
     *
     * @return bool|void
     */
    public function push()
    {
        $extra['uri']  = $this->uri;
        $extra['type'] = $this->type;

        foreach (explode(",", $this->scope_ids) as $user_id) {
            $user = User::find($user_id);
            if ($user) {
                $receiver              = new MessageReceiver;
                $receiver->receiver_id = $user_id;
                $receiver->message_id  = $this->id;
                $receiver->is_read     = 0;
                $receiver->save();
                if ($user && $user->FALIYUNTOKEN) {
                    PushRecord::send($user->FALIYUNTOKEN, "南竹通告单+", $this->title, $this->title, $extra, false);
                }
            }
        }
    }

    /**
     * 撤销消息
     */
    public function undo()
    {
        $this->update(['is_undo' => self::HAD_UNDO]);

        //删除消息接受者
        $this->receivers->delete();
    }

    /**
     * 没有撤回的通告单
     *
     * @param $query
     *
     * @return
     */
    public function scopeNotUndo($query)
    {
        return $query->where('is_undo', self::NOT_UNDO);
    }

    /**
     * 是否已经撤回
     */
    public function isUndo()
    {
        return $this->is_undo == self::HAD_UNDO;
    }

    /**
     * 一个消息可能有多个关联文件
     */
    public function files()
    {
        return $this->hasMany(MessageFiles::class, 'message_id', 'id');
    }
}
