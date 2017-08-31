<?php

namespace App\Http\Middleware;

use App\Models\Movie;
use Closure;

class UpdateDailyDataMustFulfillPast
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $movie = Movie::find($request->input('movie_id'));
        $saveDate = $request->input('FDATE');

        if (!$movie->isAllPastDaysProgressDataFullfiled($saveDate) ||
             strtotime($saveDate) < strtotime($movie->totalData->FSTARTDATE)
        ) {

            $lastDay = $movie->getNeedToProgressDay();

            return response()->json([
                'success'      => false,
                'msg'          => '每日数据未填写,请填写完整!',
                'redirect_url' => "/mobile/charts/daily?day=$lastDay&movie_id={$request->input('movie_id')}&user_id={$request->input('user_id')}"
            ]);
        }

        return $next($request);
    }
}
