<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Advertisement extends Model
{
    public $timestamps = false;
    protected $table = "t_biz_advertisement";
    protected $fillable = ["FID", "FPICURL", "FLINK", "FNAME", "FPOS"];
}
