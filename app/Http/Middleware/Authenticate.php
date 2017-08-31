<?php

namespace App\Http\Middleware;

use App\User;
use Closure;

class Authenticate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     * @param  string|null              $guard
     *
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        $user_id = $request->session()->get("user_id");
        if (!$user_id) {
            return redirect()->to('/login');
        }
        if (!User::where("FID", $user_id)->first()->has_role(1)) {
            if ($request->segment(1) == "admin") {
                return redirect()->to('/home');
            }
        }



        return $next($request);
    }
}
