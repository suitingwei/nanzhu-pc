<?php

namespace App\Http\Middleware;

use App\Models\Group;
use App\Models\GroupUser;
use App\Models\Movie;
use Closure;

class JoinMovieConditions
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
        $movie_id = $request->input("movie_id");
        $user_id  = $request->input("user_id");
        $data     = $request->all();
        $movie    = Movie::where("FID", $movie_id)->first();

        //首先判断当前剧组是否关闭
        if (!$movie || $movie->FISOROPEN != 1) {
            $errorMsg = "剧组已经关闭不允许加入";
        }

        //未进组的判断进组密码是否正确
        if ($movie->FPASSWORD != $data['password']) {
            $errorMsg = "进入失败,密码错误";
        }

        //未关闭的话在判断 当前人员是否已进组
        $group_user = GroupUser::where("FMOVIE", $movie_id)->where("FUSER", $user_id)->first();

        if ($group_user) {
            $errorMsg = "你已经加入了这个剧组";
        }

        $group = Group::where("FMOVIE", $movie_id)->where("FNAME", $data['group_name'])->first();
        if (!$group) {
            $errorMsg = "进入失败,加入的部门不存在";
        }

        if (isset($errorMsg)) {
            return redirect()->to("/mobile/result?result=" . json_encode(["ret" => -99, "msg" => $errorMsg]));
        }

        return $next($request);
    }
}
