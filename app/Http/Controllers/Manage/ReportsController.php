<?php

namespace App\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use App\Models\DailyReport;
use Illuminate\Http\Request;

/**
 * Class MessagesController
 * @package App\Http\Controllers\Manage
 */
class ReportsController extends Controller
{
    /**
     * @param Request $request
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $movie_id = $request->get("movie_id");

        $dailyReports = DailyReport::where(["movie_id" => $movie_id])->orderBy("created_at", "desc")->get();

        return view("manage.reports.index", compact('movie_id','dailyReports'));
    }

    /**
     * @param Request $request
     * @param         $id
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show(Request $request, $id)
    {
        $movie_id = $request->get("movie_id");
        $report = DailyReport::find($id);
        return view("manage.reports.show", compact('movie_id','report'));
    }

    /**
     * @param         $dailyReportId
     * @param Request $request
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function receivers($dailyReportId,Request $request)
    {
        $receivers    = [];
        $un_receivers = [];
        $dailyReport  = DailyReport::find($dailyReportId);
        $message      = $dailyReport->messages()->first();
        if ($message) {
            $ms = $message->receivers()
                          ->where("message_receivers.is_read", 0)
                          ->get();
            foreach ($ms as $receiver) {
                $rer    = [];
                $result = \DB::select("select tu.FPHONE as phone, g.FLEADERID as leader,g.FID as groupid , g.FNAME as groupname,u.FREMARK as job,tu.FNAME as username,u.FID as group_user_id,u.FOPENED from  t_biz_groupuser as u left join t_biz_group as g  on g.FID = u.FGROUP left join t_sys_user as tu on tu.FID = u.FUSER  where u.FMOVIE = " . $message->movie_id . " and u.FUSER = " . $receiver->receiver_id . " order by groupid");
                if (isset($result[0])) {
                    $rer['groupid']       = $result[0]->groupid;
                    $rer['groupname']     = $result[0]->groupname;
                    $rer['job']           = $result[0]->job;
                    $rer['phone']         = $result[0]->phone;
                    $rer['FOPENED']       = $result[0]->FOPENED;
                    $rer['group_user_id'] = $result[0]->group_user_id;
                    $rer['uid']           = $receiver->receiver_id;
                    $rer['username']      = $result[0]->username;
                    $rer['updated_at']    = $receiver->updated_at;
                    $rer['created_at']    = $receiver->created_at;
                    $rer['leader']        = $result[0]->leader;
                    $un_receivers[]       = $rer;
                }
            }
            $ms = $message->receivers()
                          ->where("is_read", 1)
                          ->orderbyRaw(" is_read , updated_at desc")
                          ->get();
            foreach ($ms as $receiver) {
                $rer    = [];
                $result = \DB::select("select tu.FPHONE as phone, g.FLEADERID as leader , g.FNAME as groupname,u.FREMARK as job,tu.FNAME as username,u.FID as group_user_id,u.FOPENED from  t_biz_groupuser as u left join t_biz_group as g  on g.FID = u.FGROUP left join t_sys_user as tu on tu.FID = u.FUSER  where u.FMOVIE = " . $message->movie_id . " and u.FUSER = " . $receiver->receiver_id);
                if (isset($result[0])) {
                    $rer['groupname']     = $result[0]->groupname;
                    $rer['job']           = $result[0]->job;
                    $rer['phone']         = $result[0]->phone;
                    $rer['FOPENED']       = $result[0]->FOPENED;
                    $rer['group_user_id'] = $result[0]->group_user_id;
                    $rer['uid']           = $receiver->receiver_id;
                    $rer['username']      = $result[0]->username;
                    $rer['updated_at']    = $receiver->updated_at;
                    $rer['created_at']    = $receiver->created_at;
                    $rer['leader']        = $result[0]->leader;
                    $receivers[]          = $rer;
                }
            }
        }
        return view("manage.reports.receivers",
            ["receivers" => $receivers, "un_receivers" => $un_receivers, 'movie_id' => $request->input('movie_id')]);
    }
}
