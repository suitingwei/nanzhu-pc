<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class ProgressDailyData
 * @package App\Models
 */
class ProgressDailyData extends Model
{
    public $table = 't_biz_progressdailydata';

    public $primaryKey = 'FID';

    //由于该死的数据库主键不是自增,需要关闭主键自增,否则返回的主键是0
    //http://stackoverflow.com/questions/25604605/laravel-eloquent-after-save-id-becomes-0
    public $incrementing = false;

    public $timestamps =false;

    public $guarded = [];

    /**
     * 每日数据由多个摄制组数据组成
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function groupDatas()
    {
        return $this->hasMany(ProgressDailyGroupData::class,'FDAILYDATAID','FID');
    }

    /**
     * 计算更新每日数据
     * 根据各个摄影组今天的(指定日期)的计划/实际拍摄场景页数
     * -----------------------------------------------
     * 1.更新[今天] [每一个摄影组] 的累计拍摄计划/实际拍摄场景页数
     * 2.更新[今天] [全剧] 的累计拍摄计划/实际拍摄场景页数
     * 3.更新[今天] [全剧] 剩余数据(场景/页数/平均拍摄等)
     */
    public function updateProgressData()
    {
        //更新今天每个摄制组的累计拍摄场景
        $this->updateGroupsProgressData();

        //更新今天的累计拍摄总场景(各个摄影组之和)
        $this->updateDailyProgressData();

        //更新剧组累计总数据
        $totalMovieProgressData = $this->updateMovieProgressData();

        //更新剧组剩余数据
        $this->updateMovieRestProgressData($totalMovieProgressData);
    }

    /**
     * 更新该摄影组的累计拍摄数据
     */
    private function updateGroupsProgressData()
    {
        for ($dailyGroupIndex = 1; $dailyGroupIndex <= 5; $dailyGroupIndex++) {

            //更新该摄影组的累计拍摄数据
            $totalGroupProgressData = ProgressDailyGroupData::getTotalGroupProgressDataTillDate(
                $dailyGroupIndex, $this->FMOVIEID,$this->FDATE);

            $this->groupDatas()->where('FGROUPID', $dailyGroupIndex)->update([
                "FSUMSCENE" => $totalGroupProgressData->FDAILYSCENE,
                "FSUMPAGE"  => $totalGroupProgressData->FDAILYPAGE
            ]);
        }
    }

    /**
     * 更新今天的剧组累计拍摄数据(各个摄影组之和)
     */
    private function updateDailyProgressData()
    {
        //获取今天的累计拍摄数据(各个摄影组之和)
        $totalDailyProgressData = $this->groupDatas()
            ->selectRaw("SUM(t_biz_progressdailygrdata.FDAILYSCENE) as sumDailyScene,
                         SUM(t_biz_progressdailygrdata.FDAILYPAGE) as sumDailyPage")
            ->first();

        $this->update([
            "FDAILYSCENE" => $totalDailyProgressData->sumDailyScene,
            "FDAILYPAGE"  => $totalDailyProgressData->sumDailyPage,
        ]);
    }

    /**
     * 更新剧组剩余拍摄数据
     * -------------------------------------
     * 1. [剩余场镜] = [总场景数]- [全剧累计场镜]
     * 2. [剩余页数] = [总页数]- [全剧累计页数]
     * 3. [剩余天数] = [总天数]-（选择的日期-开始日期+1）
     * 4. [日均场镜] = [全剧累计场镜]/拍摄耗时
     * 5. [日均页数] = [全剧累计页数]/拍摄耗时
     * 6. [此后每日需达均量（页）]=[剩余页数]/[剩余天数]
     * 7. [此后每日需达均量（场镜）]=[剩余场镜]/[剩余天数]
     *
     * @param $totalMovieProgressData
     */
    private function updateMovieRestProgressData($totalMovieProgressData)
    {
        $totaldata = ProgressTotalData::where("FMOVIEID", $this->FMOVIEID)->first();

        list($restScene, $restPage, $pastDay, $restDay) = $this->calculateRestProgressData($totalMovieProgressData, $totaldata);

        list($averageScene, $averagePage, $needDailyPage, $needDailyScene) = $this->calculateNeedProgressData($totalMovieProgressData, $pastDay, $restDay, $restPage, $restScene);

        $this->update([
            "FRESTSCENE"      => $restScene,
            "FRESTPAGE"       => $restPage,
            "FRESTDAY"        => $restDay,
            "FAVERAGESCENE"   => $averageScene,
            "FAVERAGEPAGE"    => $averagePage,
            "FNEEDDAILYPAGE"  => $needDailyPage,
            "FNEEDDAILYSCENE" => $needDailyScene
        ]);
    }

    /**
     * 更新剧组总数据
     *
     * @return mixed
     */
    private function updateMovieProgressData()
    {
        $totalMovieProgressData = ProgressDailyGroupData::getMovieTotalProgressDataTillDate($this->FMOVIEID, $this->FDATE);

        $this->update([
            "FALLPAGE"    => $totalMovieProgressData->allPage,
            "FTOTALSCENE" => $totalMovieProgressData->allScene
        ]);

        return $totalMovieProgressData;
    }

    /**
     * @param $totalMovieProgressData
     * @param $totaldata
     *
     * @return array
     */
    private function calculateRestProgressData($totalMovieProgressData, $totaldata)
    {
        $restScene = $totaldata->FTOTALSCENE - $totalMovieProgressData->allScene;
        $restPage  = $totaldata->FTOTALPAGE - $totalMovieProgressData->allPage;

        //如果总页数不填写,就保存为null,前端不显示
        if(intval($totaldata->FTOTALSCENE) ==0 ){
            $restScene = null;
        }
        //同总场景
        if(intval($totaldata->FTOTALPAGE) == 0){
            $restPage = null;
        }

        $currentDay = Carbon::createFromTimestamp(strtotime($this->FDATE));
        $startDay   = Carbon::createFromTimestamp(strtotime($totaldata->FSTARTDATE));
        $pastDay    = $currentDay->diffInDays($startDay) + 1;
        $restDay    = $totaldata->FTOTALDAY - $pastDay;
        return array($restScene, $restPage, $pastDay, $restDay);
    }

    /**
     * @param $totalMovieProgressData
     * @param $pastDay
     * @param $restDay
     * @param $restPage
     * @param $restScene
     *
     * @return array
     */
    private function calculateNeedProgressData($totalMovieProgressData, $pastDay, $restDay, $restPage, $restScene)
    {
        $averageScene = ($pastDay != 0) ? round($totalMovieProgressData->allScene / $pastDay, 2) : 0;
        $averagePage  = ($pastDay != 0) ? round($totalMovieProgressData->allPage / $pastDay, 2) : 0;

        $needDailyPage  = ($restDay != 0) ? round($restPage / $restDay, 2) : 0;
        $needDailyScene = ($restDay != 0) ? round($restScene / $restDay, 2) : 0;

        if(is_null($restPage)){
            $needDailyPage= null;
        }

        if(is_null($restScene)){
            $needDailyScene= null;
        }
        return array($averageScene, $averagePage, $needDailyPage, $needDailyScene);
    }
}
