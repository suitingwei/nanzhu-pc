<?php

namespace App\Traits;

/**
 * 用于老表不能指定数据库主键的model,
 * 添加Find方法
 * Class ModelFindTrait
 */
trait ModelFindTrait
{
    public static function find($id)
    {
        return static::where('FID', $id)->first();
    }
}
