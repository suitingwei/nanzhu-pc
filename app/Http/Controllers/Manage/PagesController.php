<?php

namespace App\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\MessageReceiver;
use App\User;
use Illuminate\Http\Request;

class PagesController extends Controller
{

    public function index(Request $request)
    {
        $movie_id = $request->get("movie_id");
        $pages    = Message::where(["type" => "BLOG", "movie_id" => $movie_id])->orderBy("created_at", "desc")->get();
        return view("manage.pages.index", compact('movie_id', 'pages'));
    }

    public function create(Request $request)
    {
        $movie_id                      = $request->get("movie_id");
        $allowedUploadFileTypes        = Message::feiyeUploadFileAllowedTypes();
        $allowedUploadFileTypesWithDot = Message::feiyeUploadFileAllowedTypes(true);

        return view("manage.pages.create", compact("movie_id", 'allowedUploadFileTypes','allowedUploadFileTypesWithDot'));
    }

    public function show(Request $request, $id)
    {
        $page  = Message::find($id);
        $FID   = $page->from;
        $user  = User::find($FID);
        $from  = $user->FNAME;
        $gname = $user->groupNamesInMovie($page->movie_id);
        if (!$gname) {
            $gname = '无部门';
        }
        $movie_id = $page->movie_id;
        return view("manage.pages.show", compact('movie_id', 'page', 'from', 'gname'));
    }

    public function receivers(Request $request, $id)
    {
        $movie_id     = $request->get("movie_id");
        $receivers    = [];
        $un_receivers = [];
        $message      = Message::find($id);
        if ($message) {
            $ms = MessageReceiver::where("message_receivers.message_id",
                $message->id)->where("message_receivers.is_read", 0)->get();
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

            $ms = MessageReceiver::where("message_receivers.message_id", $message->id)->where("is_read",
                1)->orderbyRaw(" is_read , updated_at desc")->get();
            foreach ($ms as $receiver) {
                $rer    = [];
                $result = \DB::select("select 
                                       tu.FPHONE as phone, 
                                       g.FLEADERID as leader , 
                                       g.FNAME as groupname,
                                       u.FREMARK as job,
                                       tu.FNAME as username,
                                       u.FID as group_user_id,
                                       u.FOPENED 
                                       from  t_biz_groupuser as u 
                                       left join t_biz_group as g  
                                       on g.FID = u.FGROUP 
                                       left join t_sys_user as tu 
                                       on tu.FID = u.FUSER  
                                       where u.FMOVIE = " . $message->movie_id . " 
                                       and u.FUSER = " . $receiver->receiver_id);
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
        return view("manage.messages.receivers", [
            "movie_id"     => $movie_id,
            "receivers"    => $receivers,
            'un_receivers' => $un_receivers,
            'movieId'      => $request->input('movie_id')
        ]);
    }
}
