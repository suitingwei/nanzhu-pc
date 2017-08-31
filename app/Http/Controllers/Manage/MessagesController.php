<?php

namespace App\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\MessageReceiver;
use App\Models\Picture;
use App\User;
use Illuminate\Http\Request;

/**
 * Class MessagesController
 * @package App\Http\Controllers\Manage
 */
class MessagesController extends Controller
{

    /**
     * @param Request $request
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $movie_id = $request->get("movie_id");
        $messages = Message::where(["type" => "JUZU", "movie_id" => $movie_id])->orderBy("created_at", "desc")->get();
        return view("manage.messages.index", ["messages" => $messages, "movie_id" => $movie_id]);
    }

    /**
     * @param Request $request
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create(Request $request)
    {
        $movie_id = $request->get("movie_id");
        $allowedUploadFileTypes        = Message::feiyeUploadFileAllowedTypes();
        $allowedUploadFileTypesWithDot = Message::feiyeUploadFileAllowedTypes(true);
        return view("manage.messages.create", compact('movie_id','allowedUploadFileTypes','allowedUploadFileTypesWithDot'));
    }

    /**
     * @param Request $request
     * @param         $id
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show(Request $request, $id)
    {
        $message = Message::find($id);
        $user    = User::find($message->from);
        $from    = $user->FNAME;
        $gname   = $user->groupNamesInMovie($message->movie_id);
        if (!$gname) {
            $gname = '无部门';
        }
        $movie_id = $message->movie_id;
        return view("manage.messages.show", compact('movie_id', 'message', 'from', 'gname'));
    }

    /**
     * @param Request $request
     * @param         $id
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
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

    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function upload(Request $request)
    {
        set_time_limit(0);
        $uploadFile = $request->file('file');

        $url         = Picture::upload('messages' . time(), $uploadFile);
        $fileType    = $uploadFile->getClientOriginalExtension();
        $fileName    = $uploadFile->getClientOriginalName();
        $isImageType = in_array(strtolower($fileType), Message::$feiyeUploadFileImageTypes);

        return response()->json([
            'success' => true,
            'msg'     => '',
            'data'    => [
                'uploaded_file_url' => $url,
                'is_image_type'     => $isImageType,
                'file_type'         => $fileType,
                'file_name'         => $fileName
            ]
        ]);
    }


    /**
     * 创建新的剧组通知
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        Message::createNewJuzuNofity($request, $request->input('user_id'));

        if ($request->get('type') == 'JUZU') {
            return redirect()->to('/manage/messages?movie_id=' . $request->input('movie_id'));
        } elseif ($request->get('type') == "BLOG") {
            return redirect()->to('/manage/pages?movie_id=' . $request->input('movie_id'));
        }
    }


    /**
     * 撤销发送剧组通知,剧本扉页
     *
     * @param Request $request
     * @param         $id
     *
     * @return mixed
     */
    public function redo(Request $request, $id)
    {
        $data    = $request->all();
        $message = Message::find($id);
        if ($message) {
            $message->update(["is_undo" => 1]);
            MessageReceiver::where("message_id", $message->id)->where("receiver_id", "<>", $data['user_id'])->delete();
        }
        return response()->json([
            'success' => true,
            'msg'     => '',
            'data'    => []
        ]);

    }

}
