<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ReferencePlan extends Model
{
    public $table = "reference_plans";
    protected $fillable = ["movie_id", "file_url", "title","file_name","file_path","creator_id"];
    public function plans_is_send($FID)
    {
        $excel_is_send = DB::table('messages')
            ->where('plan_id', $FID)
            ->where('type','PLAN')
            ->where('is_undo','<>',1)
            ->count();
        if ($excel_is_send > 0) {
            return true;
        }
        return false;
    }
}
