<?php

namespace App\Models;

use App\Traits\DeleteGroupUserTrait;
use Illuminate\Database\Eloquent\Model;

class ProgressPower extends Model
{
    use DeleteGroupUserTrait;

    protected $table = 't_biz_progresspower';

    protected $primaryKey = 'FID';

    public $timestamps = false;

    protected $fillable = ['FGROUPUSERID', 'FMOVIEID', 'FID', 'FISNEWDATA'];

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

    /**
     * 添加一个新的groupuser到movie的进度权限
     *
     * @param $groupUserId
     * @param $movieId
     */
    public static function assignToGroupuserInMovie($groupUserId, $movieId)
    {
        self::create([
            'FGROUPUSERID' => $groupUserId,
            'FMOVIEID'     => $movieId,
            'FISNEWDATA'   => 0,
            'FID'          => self::max("FID") + 1
        ]);
    }


}
