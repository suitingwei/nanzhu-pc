<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NoticeRecord extends Model
{
    protected $fillable = ["movie_id", "user_id", "team_id","notice_id","notice_file_id","original_file_name","new_file_name","editor"];
}
