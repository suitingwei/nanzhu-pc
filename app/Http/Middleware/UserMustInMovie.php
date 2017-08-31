<?php

namespace App\Http\Middleware;

use App\User;
use Closure;

class UserMustInMovie
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
		$movieId = $request->input('movie_id');
        $userId  = $request->input('user_id');
        $user    = User::find($userId);

        if($user && ! $user->isInMovie($movieId)){
            return view('errors.user_not_in_movie');
		}

        return $next($request);
    }
}
