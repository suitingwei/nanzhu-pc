<?php

namespace App\Http\Middleware;

use App\User;
use Closure;
use Illuminate\Support\Facades\Session;

class ManageMiddleware
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
        $tongchou = [];
        $user_id  = $request->session()->get("user_id");

        if (!$user_id) {
            return redirect()->to('/login');
        }

        $joinedMovies = User::find($user_id)->joinedMovies()->all();
        foreach ($joinedMovies as $k => $v) {
            $tongchou[$k] = User::find($request->session()->get("user_id"))->isTongChouInMovie($v->FID);
        }

        if (!in_array('true', $tongchou)) {
            Session::put('message', '对不起，您还不是统筹部门人员');
            return back();
        }

        return $next($request);
    }
}
