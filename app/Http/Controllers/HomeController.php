<?php

namespace App\Http\Controllers;

use App\Models\Notice;
use App\User;
use Bican\Roles\Models\Role;
use DB;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
		\Log::info($request->url());
        if ($request->session()->has("user_id")) {
            //$this->checkPermission($request);
            $user_id = $request->session()->get("user_id");
            $phone   = User::where("FID", $user_id)->first()->FPHONE;
            $movies  = DB::select("select mu.FMOVIE,su.FNAME from t_biz_movieuser  as mu  left join t_sys_user  su on mu.FUSER=su.FID left join t_biz_movie as movie on mu.FMOVIE=movie.FID where movie.shootend=0 and su.FPHONE ='" . $phone . "'");
            if (count($movies) > 0) {
                $request->session()->put("movies", $movies);
                $movie_id = $request->get("movie_id");
                if (!$movie_id) {
                    $movie_id = $movies[0]->FMOVIE;
                }
                $notices = Notice::where("FMOVIEID", $movie_id)->orderby("FID", "DESC")->get();
                return view('website.home', ["movies" => $movies, "notices" => $notices, "movie_id" => $movie_id]);
            }

        }
        return redirect()->to("/logout")->with("message", "暂时无权限");
    }

    public function state(Request $request)
    {

        $movie_id = $request->get("movie_id");
        if ($movie_id) {
            $request->session()->put("movie_id", $movie_id);
        }
        return view('website.stat');
    }

    public function welcome(Request $request)
    {
        if ($request->url()=="http://www.nanzhuxinyu.com") {
            return redirect()->to("/website/index.html");
        }

	return redirect()->to("/login");
    }


}
