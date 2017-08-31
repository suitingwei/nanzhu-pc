<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $table = 't_biz_comment';

    protected $fillable = ['FREPLYCONTENT'];

    public $timestamps = false;
}
