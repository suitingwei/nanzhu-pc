<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Class HxGroupPublicNotice
 * @package App\Models
 * 环信群组的群公告
 */
class HxGroupPublicNotice extends Model
{
    public $guarded = [];

    public $appends = ['editor'];

    public function getEditorAttribute()
    {
        return User::find($this->editor_id)->formatBasicClass()->get();
    }
}
