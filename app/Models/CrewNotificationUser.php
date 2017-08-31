<?php

namespace App\Models;

use App\Traits\DeleteGroupUserTrait;
use Illuminate\Database\Eloquent\Model;


/**
 * 剧组通知
 * Class CrewNotificationUser
 * @package App
 */
class CrewNotificationUser extends Model
{
    use DeleteGroupUserTrait;

    public $timestamps = false;

    protected $primaryKey = 'FID';

    protected $table = 't_biz_tzuser';

}
