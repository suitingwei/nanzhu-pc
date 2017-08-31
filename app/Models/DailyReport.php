<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class DailyReport extends Model
{
    /**
     * 拼接日报表title
     */
    public function getTitleAttribute()
    {
        $date = Carbon::createFromFormat('Y-m-d H:i:s', $this->date)->format('Y年m月d日');

        return "{$this->group}组 {$date}({$this->chinese_week_day})";
    }

    /**
     * 获取周一周二这样的属性
     * @return string
     */
    public function getChineseWeekDayAttribute()
    {
        static $chineseWeekDay = ['日', '一', '二', '三', '四', '五', '六'];

        return $chineseWeekDay[$this->created_at->dayOfWeek];
    }

    /**
     * 场记日报表和其他的剧组通知,剧本扉页不一样,这东西没有撤回发送
     * 只有当更新内容的时候让所有人重新编程未读
     */
    public function messages()
    {
        return $this->hasMany(Message::class, 'daily_report_id', 'id');
    }

    /**
     * 一个日报表可能有多个图片
     */
    public function pictures()
    {
        return $this->hasMany(Picture::class, 'daily_report_id', 'id');
    }

}
