<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Like extends Model
{
    //

    protected $fillable = ["like_id", "user_id", "type"];

    public function scopeUserType($query)
    {
        return $query->where('type','user');
    }

}
