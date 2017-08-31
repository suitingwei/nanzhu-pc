<?php

namespace App\Models;

use App\Traits\Power\PowerOperation;
use Illuminate\Database\Eloquent\Model;

class DailyReportPower extends Model
{
    public $fillable = ['group_user_id', 'movie_id'];

    /**
     * Get the database group-user key,because the old power table contains the
     * different key from the new power table.
     * @return string
     */
    public static function getGroupUserKey()
    {
        return 'group_user_id';
    }

    /**
     * Get the database movie key,because the old power table contains the
     * different key from the new power table.
     * @return string
     */
    public static function getMovieKey()
    {
        return 'movie_id';
    }
}
