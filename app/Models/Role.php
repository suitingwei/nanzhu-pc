<?php

namespace App\Models;

use DB;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $fillable = ['name', 'code'];

    public static function add_user($user_id, $role_id)
    {
        $data = DB::table("role_user")->where("user_id", $user_id)->where("role_id", $role_id)->first();
        if ($data) {
            return $data;
        }
        $data["user_id"] = $user_id;
        $data["role_id"] = $role_id;
        $data            = DB::insert("insert into role_user (user_id, role_id) values (?, ?)", [$user_id, $role_id]);
        return $data;

    }

    public function permissions()
    {

    }
}
