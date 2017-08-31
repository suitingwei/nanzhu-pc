<?php

namespace App\Models;

use App\Traits\DeleteGroupUserTrait;
use Illuminate\Database\Eloquent\Model;

class ReceivePower extends Model
{
    use DeleteGroupUserTrait;

    protected $table = 't_biz_nereceivepower';

    protected $primaryKey = 'FID';

    public $timestamps = false;

    protected $fillable = ['FGROUPUSERID', 'FMOVIEID','FID'];

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
