<?php

namespace App\Models;

use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    //默认的业内动态图片
    const DEFAULT_TREND_COVER_URL = 'http://nanzhu.oss-cn-shanghai.aliyuncs.com/pictures/1681114460.png';

    protected $fillable = ["author_id", "type", "title", "content", "type_value", "is_approved", "approved_opinion"];

    public static function type_arr()
    {
        return [
            "news" => ["全部", "新闻", "辟谣", "公告"],
            "juzu" => ["全部", "院线电影", "电视剧", "综艺","数字电影", "网络大电影", "网剧", "广告", "演唱会", "舞台剧", "纪录片", "真人秀", "选秀","短片"]
        ];
    }

    public static function type_desc()
    {
        return ["news" => "业内信息", "juzu" => "剧组动态"];
    }

    public function toArray()
    {

        $array['id']     = $this->id;
        $array['author'] = "南竹通告单";
        if ($this->author_id > 0) {
            $user = User::select("FNAME")->where("FID", $this->author_id)->first();
            if ($user) {
                $array['author'] = $user->FNAME;
            }
        }

        $array['type']             = $this->type;
        $array['approved_opinion'] = $this->approved_opinion;
        $array['content']          = $this->content;
        $array['is_approved']      = $this->is_approved;
        $array["title"]            = $this->title;
        $carbon                    = Carbon::createFromTimestamp(strtotime($this->created_at));
        $carbon->setLocale("zh");
        $array["created_at"]  = $carbon->diffForHumans();
        $array["d"]           = substr($carbon->toDateTimeString(), 0, 10);
        $array['type_value']  = $this->type_value;
        $array['trend_cover'] = $this->trend_cover ? $this->trend_cover : '';
        $array['pictures']    = $this->pictures();

        return $array;
    }

    public function pictures()
    {
        $arr      = [];
        $pictures = Picture::select("url")->where("blog_id", $this->id)->get();
        $count    = Picture::where("blog_id", $this->id)->count();
        if ($count > 0) {
            foreach ($pictures as $picture) {
                $arr[] = $picture->url;
            }
        } else {
            $arr[] = self::DEFAULT_TREND_COVER_URL;
        }
        return $arr;
    }

    /**
     * 没有删除的
     *
     * @param $query
     *
     * @return
     */
    public function scopeNotDeleted($query)
    {
        return $query->where('is_delete', 0);
    }

    /**
     * 被审核通过的
     *
     * @param $query
     *
     * @return
     */
    public function scopeApproved($query)
    {
        return $query->where('is_approved', 1);
    }

    /**
     * 业内动态
     *
     * @param $query
     *
     * @return
     */
    public function scopeNews($query)
    {
        return $query->where('type', 'news');
    }
}
