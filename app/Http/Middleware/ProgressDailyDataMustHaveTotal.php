<?php

namespace App\Http\Middleware;

use App\Models\Des;
use App\Models\ProgressTotalData;
use Closure;
use Illuminate\Http\Request;

class ProgressDailyDataMustHaveTotal
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $movieId    = $request->get("movie_id");
        $userId     = $this->current_user($request);
        $currentDay = $this->currentDate($request);
        $movieHaveTotalData = ProgressTotalData::where("FMOVIEID", $movieId)->count() > 0;

        if (!$movieHaveTotalData) {
            return view("errors.daily_data_without_total", [
                "user_id"  => $userId,
                "movie_id" => $movieId,
                "day"      => $currentDay,
            ]);
        }

        return $next($request);
    }


    public function current_user(Request $request)
    {
        $user_token = $request->header("X-Auth-Token");
        $user_id = Des::tokenToUserId($user_token);
        if ($user_id && $user_id != -1) {
            return $user_id;
        }
        if($user_id == -1){
            return  $request->input('user_id');
        }
    }


    /**
     * 获取当前日期
     * 如果有请求参数返回请求天数
     *
     * @param Request $request
     *
     * @return array|false|string
     */
    public function currentDate(Request $request)
    {
        return $request->has('day') ? $request->input('day') : date('Y-m-d');
    }
}
