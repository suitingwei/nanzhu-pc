<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Recruit extends Model
{

    protected $fillable = ["type", "is_approved", "title", "content", "author_id", "employer", "count"];

    public function toArray()
    {
        $array['type']      = $this->type;
        $array['title']     = $this->title;
        $array['content']   = $this->content;
        $array['id']        = $this->id;
        $array['author_id'] = $this->author_id;
        $array['employer']  = $this->employer;
        $array['count']     = $this->count;
        $carbon             = \Carbon\Carbon::createFromTimeStamp(strtotime($this->created_at));
        $carbon->setLocale("zh");
        $array["created_at"] = $carbon->diffForHumans();
        $array["d"]          = substr($carbon->toDateTimeString(), 0, 10);
        $array['pic_urls']   = $this->pic_urls();
        $array['is_favorite'] = false;
        return $array;
    }

    public function pic_urls()
    {
        $arr      = [];
        $pictures = Picture::where("recruit_id", $this->id)->get();

        $count = Picture::where("recruit_id", $this->id)->count();
        if ($count > 0) {
            foreach ($pictures as $picture) {
                $arr[] = $picture->url;
            }
        }
        return $arr;
    }
}
