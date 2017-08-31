<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * 剧组通知,剧本扉页上传的文件
 * Class MessageFiles
 * @package App\Models
 */
class MessageFiles extends Model
{
    const TYPE_JUZU  = 'JUZU';
    const TYPE_FEIYE = 'FEIYE';


    public $guarded = [];

    /**
     * 一个消息文件属于一个消息
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function message()
    {
        return $this->belongsTo(Message::class, 'message_id', 'id');
    }


}
