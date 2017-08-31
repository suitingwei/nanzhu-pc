<?php

namespace App\Models;

use App\Traits\DeleteGroupUserTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int  FChecked
 * @property int  FGROUPUSERID
 * @property int  FREGPHONE
 * @property int  FPHONE
 * @property  int FPOS
 */
class SparePhone extends Model
{
    const  PHONE_REGISTER = 1;  //注册电话
    const  PHONE_NORMAL   = 0;  //普通电话

    const  CHECKED_ON  = 1;    //允许查看
    const  CHECKED_OFF = 0;    //不允许查看

    const DEFAULT_PHONE_NUMBER = null;  //默认电话号码

    const TOTAL_PHONE_NUMBER_PER_PERSON = 3; //默认每个人最多有三个电话

    protected $table = 't_biz_sparephone';

    protected $primaryKey = 'FID';

    public $timestamps = false;

    protected $fillable = [
        'FGROUPUSERID',
        'FChecked',
        'FREGPHONE',
        'FPHONE',
        'FPOS',
        'FNEWDATE',
        'FEDITDATE',
        'FID'
    ];

    /**
     * 查询公开电话
     *
     * @param $query
     *
     * @return mixed
     */
    public function scopeChecked($query)
    {
        return $query->where('FChecked', 1);
    }

    /**
     * 给组员创建普通电话
     * @param $groupUser
     * @param $pos
     */
    public static function createNormalPhone($groupUser,$pos)
    {
        self::create([
            'FGROUPUSERID' => $groupUser->FID,
            'FChecked'     => SparePhone::CHECKED_OFF,
            "FREGPHONE"    => SparePhone::PHONE_NORMAL,
            "FPHONE"       => '',
            "FPOS"         => $pos,
            "FNEWDATE"     => date('Y-m-d H:i:s'),
            "FEDITDATE"    => date('Y-m-d H:i:s'),
            'FID' => self::max('FID') +1
        ]);
    }
}
