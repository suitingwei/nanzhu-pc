<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    //

    protected $fillable = ['title', "content", 'cover', 'url', 'sort'];

    public function toArray()
    {
        $array['title']      = $this->title;
        $array['content']    = $this->content;
        $array['cover']      = $this->cover;
        $array['sort']       = $this->sort;
        $array['type']       = $this->type;
        $array['url']        = $this->url;
        $array['created_at'] = $this->created_at;
        return $array;
    }
}
