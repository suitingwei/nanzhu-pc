<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Roleuser extends Model
{
    protected $table = "role_user";
    protected $fillable = ["id", "role_id", "user_id", "created_at", "updated_at"];
}
