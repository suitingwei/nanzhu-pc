<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Group;
use App\Models\Movie;
use App\User;
use DB;
use Illuminate\Http\Request;
use Log;

class AuthController extends Controller
{

    public function showLoginForm(Request $request)
    {
        return view("auth.login", ['request' => $request]);
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
        try {
            list($user, $movies) = $this->validateInput($request);
        } catch (\Exception $e) {
            $request->session()->put('message', $e->getMessage());
            \Log::info('登录失败' . $e->getMessage());
            return redirect()->to("/login");
        }

        $request->session()->put('user_id', $user->FID);
        $request->session()->put('user', $user);
        $request->session()->put('movies', $movies);

        if ($user->isTongChouInMovie($movies->first()->FID)) {
            return redirect()->to('/manage/notices?movie_id=' . $movies->first()->FID);
        }

        return redirect()->to('/manage/messages?movie_id=' . $movies->first()->FID);
    }

    public function logout(Request $request)
    {
        $request->session()->forget('user_id');
        $request->session()->forget('user');
        $request->session()->forget('movies');
        $request->session()->forget('message');
        return redirect()->to("/login")->with("message", "成功登出");
    }

    /**
     * 获取用户所有作为统筹制片导演身份的部门的剧组
     *
     * @param User $user
     *
     * @return
     */
    private function getAllAllowedMovies(User $user)
    {
        $movieIds = Group::whereIn('FID', $user->groupUsers()->selectRaw('distinct FGROUP')->lists('FGROUP')->all())
                         ->where(function ($query) {
                             $query->where('FNAME', 'like', '%统筹%')
                                   ->orWhere('FNAME', 'like', '%制片%')
                                   ->orWhere('FNAME', 'like', '%导演%');
                         })->selectRaw('distinct FMOVIE')->lists('FMOVIE')->all();

        return Movie::whereIn('FID', $movieIds)->where('shootend', 0)->get();
    }

    /**
     * @param Request $request
     *
     * @return array
     * @throws \Exception
     */
    private function validateInput(Request $request)
    {
        if (!$request->input('phone')) {
            throw new \Exception('请输入手机号');
        }
        if (!$request->input('code')) {
            throw new \Exception('请输入验证码');
        }
        if (!User::checkPhone($request->input('phone'))) {
            throw new  \Exception('您还不是南竹用户,请先注册后才能使用');
        }
        $data = User::login_or_register($request->input('phone'), $request->input('code'));

        if ($data['ret'] != 0) {
            throw new  \Exception('验证码或者手机号错误');
        }

        $user = $data['user'];

        //统筹制片导演部门的人可以登录
        if (!$user->isTongchou() &&
            !$user->isZhiPian() &&
            !$user->isDirector()
        ) {
            throw new  \Exception('对不起，您还不是统筹,制片,导演任一部门人员');
        }

        //获取所有是统筹制片导演身份的剧组
        $movies = $this->getAllAllowedMovies($user);

        if ($movies->count() == 0) {
            throw new \Exception('没有正在拍摄的剧组');
        }

        return [$user, $movies];
    }

    /**
     * @param         $user
     * @param Request $request
     */
    private function cacheSession($user, Request $request)
    {
        $request->session()->put('user_id', $user->FID);
        $request->session()->put('user', $user);
    }
}
