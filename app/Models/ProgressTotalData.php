<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProgressTotalData extends Model
{

    public $table = 't_biz_progresstotaldata';

    protected $primaryKey = 'FID';

    public $timestamps = false;

    public $guarded =  [];

    //由于该死的数据库主键不是自增,需要关闭主键自增,否则返回的主键是0
    //http://stackoverflow.com/questions/25604605/laravel-eloquent-after-save-id-becomes-0
    public $incrementing = false;
}
