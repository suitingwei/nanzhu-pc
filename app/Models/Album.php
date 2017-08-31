<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Album extends Model
{
    //
    protected $fillable = ["title", "color", "profile_id"];

    public function toArray()
    {
        $array["title"]    = $this->title;
        $array["color"]    = $this->color;
        $array["pictures"] = $this->pictures();
        return $array;
    }

    //for json
    public function pictures()
    {
        $pictures = Picture::where("album_id", $this->id)->get();

        $count = Picture::where("album_id", $this->id)->count();
        $arr   = array();
        if ($count > 0) {
            foreach ($pictures as $picture) {
		if($picture->url){
                   $arr[] = $picture->url;
		}
            }
        }
        return $arr;
    }
}
