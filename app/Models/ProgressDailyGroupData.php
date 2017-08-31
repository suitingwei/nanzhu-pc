<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgressDailyGroupData extends Model
{

    public $table = 't_biz_progressdailygrdata';

    protected $primaryKey = 'FID';

    public $timestamps = false;

    public $appends= 'group_name'; //摄制组A/B/C

    private $groupNamesArray =  ['A','B','C','D','E'];

    //由于该死的数据库主键不是自增,需要关闭主键自增,否则返回的主键是0
    //http://stackoverflow.com/questions/25604605/laravel-eloquent-after-save-id-becomes-0
    public $incrementing = false;

    public $guarded=[];

    /**
     * 获取摄制组名称信息
     * 返回A/B/C
     */
    public function getGroupNameAttribute()
    {
        return $this->groupNamesArray[$this->FGROUPID];
    }


    /**
     * 获取剧组某摄制组某天的累计拍摄场景
     *
     * @param $groupIndex
     * @param $movieId
     * @param $date
     *
     * @return
     */
    public static function getTotalGroupProgressDataTillDate($groupIndex,$movieId,$date)
    {
        return self::leftjoin("t_biz_progressdailydata", "t_biz_progressdailydata.FID", "=",
                "t_biz_progressdailygrdata.FDAILYDATAID")
                ->where("t_biz_progressdailydata.FMOVIEID", $movieId)
                ->where("t_biz_progressdailydata.FDATE", "<=", $date)
                ->where("t_biz_progressdailygrdata.FGROUPID", $groupIndex)
                ->selectRaw("SUM(t_biz_progressdailygrdata.FDAILYSCENE) as FDAILYSCENE,
                     SUM(t_biz_progressdailygrdata.FDAILYPAGE) as FDAILYPAGE")
                ->first();
    }

    /**
     * 获取剧组某天的累计拍摄场景
     * @param $movieId
     * @param $date
     *
     * @return mixed
     */
    public static function getMovieTotalProgressDataTillDate($movieId,$date)
    {
       return self::leftjoin("t_biz_progressdailydata", "t_biz_progressdailydata.FID", "=", "t_biz_progressdailygrdata.FDAILYDATAID")
            ->where("t_biz_progressdailydata.FMOVIEID", $movieId)
            ->where("t_biz_progressdailydata.FDATE", "<=", $date)
            ->selectRaw("SUM(t_biz_progressdailygrdata.FDAILYSCENE) as allScene,
                         SUM(t_biz_progressdailygrdata.FDAILYPAGE) as allPage")
            ->first();
    }

}
