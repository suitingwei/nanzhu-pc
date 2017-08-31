<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NoticeExcel extends Model
{
    protected $table = "t_biz_noticeexcelsinfo";

    protected $fillable = ['FID,FFILEADD,FNUMBER,FORSEND,custom_group_name'];

    public $timestamps = false;

    public $appends = ['group_name', 'read_rate'];

    /**
     * @param $id
     *
     * @return NoticeExcel
     */
    public static function find($id)
    {
        return static::where('FID', $id)->first();
    }

    /**
     * 一个通告单文件对应一个通告单
     */
    public function notice()
    {
        return $this->belongsTo(Notice::class, 'FNOTICEEXCELID', 'FID');
    }

    /**
     * 判断文件是否发送
     * @return bool
     */
    public function is_send()
    {
        return Message::where("notice_file_id", $this->FID)->count() > 0;
    }

    /**
     * 一个通告单文件可以发送多条消息
     */
    public function messages()
    {
        return $this->hasMany(Message::class, 'notice_file_id', 'FID')
                    ->where('messages.notice_id', $this->notice->FID)
                    ->orderBy('id', 'desc');
    }

    /**
     * 获取通告单文件所属的组别
     * A/B/C
     */
    public function getGroupNameAttribute()
    {
        return implode(',', Notice::NOTICE_GROUPS)[$this->FNUMBER - 1] . '组通告单';
    }

    /**
     * 返回通告单接受比率
     * @return int
     */
    public function getReadRateAttribute()
    {
        return $this->readRate();
    }

    /**
     * 获取文件url
     *
     * @param $userId
     *
     * @return string
     */
    public function getFileUrl($userId)
    {
        return "/mobile/notices/{$this->notice->FID}?excel_id={$this->FID}&user_id={$userId}&filename={$this->FFILEADD}";
    }

    /**
     * 通告单文件的阅读比例
     */
    public function readRate()
    {
        if ($this->messages->count() > 0) {
            return $this->messages()->first()->readRate();
        }
        return 0;
    }
}
