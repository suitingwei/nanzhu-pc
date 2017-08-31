<?php

namespace App\Models;

use App\Traits\DeleteGroupUserTrait;
use Illuminate\Database\Eloquent\Model;

class ContactPower extends Model
{
    //删除组员时剥夺权限
    use DeleteGroupUserTrait;

    public $timestamps = false;

    protected $primaryKey = 'FID';

    protected $table = 't_biz_contactpower';

    protected $fillable = ['FGROUPUSERID', 'FMOVIEID', 'FID'];

    public $incrementing = false;
    /**
     * 删除某剧组所有权限
     *
     * @param $id
     */
    public static function clearAllPowerInMovie($id)
    {
        static::where('FMOVIEID', $id)->delete();
    }

}
