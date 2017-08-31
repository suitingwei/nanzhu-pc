<?php

namespace App\Models;

use App\Traits\DeleteGroupUserTrait;
use Illuminate\Database\Eloquent\Model;

/**
 * 每日通告单
 * Class DailyReceive
 * @package App
 */
class DailyReceive extends Model
{
    use DeleteGroupUserTrait;

    public $timestamps = false;

    protected $primaryKey = 'FID';

    protected $table = 't_biz_dailynereceive';


}
