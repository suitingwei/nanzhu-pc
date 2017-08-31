<?php

namespace App\Http\Middleware;

use Closure;

class MobilePermissionIndex
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

        //必须是最高权限的用户才能操作


        return $next($request);
    }
}
