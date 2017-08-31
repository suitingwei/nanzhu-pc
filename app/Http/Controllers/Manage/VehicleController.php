<?php

namespace App\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use App\Models\UserLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VehicleController extends Controller
{

    public function index(Request $request)
    {
        $movie_id = $request->get("movie_id");
        return view("manage.vehicle.index",compact('movie_id'));


    }

    public function ajaxUserLocations(Request $request)
    {
        $movieId = $request->input('movie_id');
        $groupId = $request->input('group_id');

        $users   = DB::select("select distinct FUSER from t_biz_groupuser where FGROUP in(
                select FID from t_biz_group where FMOVIE = {$movieId} and FNAME like '%车辆%')
              )"
        );
        $userIds = array_column($users, 'FUSER');

        $userLocations = UserLocation::whereIn('user_id', $userIds)->orderBy('created_at', 'desc')->get();

        $results = [];

        foreach ($userLocations as $userLocation) {
            if (in_array($userLocation->user_id, array_column($results, 'user_id'))) {
                continue;
            }
            array_push($results, [
                'user_id'   => $userLocation->user_id,
                'user_name' => $userLocation->user->FNAME,
                'longitude' => $userLocation->longitude,
                'latitude'  => $userLocation->latitude,
            ]);
        }

        return response()->json(['locations'=>$results]);

    }


}
