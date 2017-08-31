<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProfileRecords extends Model
{
    protected $table = "profile_records";
    protected $fillable = ["id", "user_id", "profile_id", "updated_at", "created_at"];
}
