<?php

namespace App\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Movie;
use App\Models\Notice;
use App\Models\NoticeExcel;
use App\Models\NoticeRecord;
use App\Models\Picture;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class NoticesController extends Controller
{
    private $allowUploadFileTypes = [
        'doc',
        'docx',
        'xls',
        'xlsx',
        'pdf',
        'jpg',
        'png'
    ];

    /**
     * @param Request $request
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        if ($request->session()->has("user_id") &&
            $request->session()->has('movies')
        ) {
            $user    = User::find($request->session()->get('user_id'));
            $movies  = $request->session()->get('movies');
            $movieId = $request->input('movie_id') ?: $movies[0]->FID;

            if (!$user->isTongChouInMovie($movieId)) {
                return redirect()->to('/manage/messages?movie_id=' . $movieId);
            }
            $notices = Notice::where("FMOVIEID", $movieId)->orderby("FNEWDATE", "desc")->get();
            return view('manage.notices.index', [
                "notices"  => $notices,
                "movie_id" => $movieId,
            ]);
        }

        return redirect()->back();
    }

    public function create(Request $request)
    {
        $movie_id = $request->get("movie_id");
        if ($movie_id) {
            $movie         = Movie::find($movie_id);
            $notice_excels = Notice::where("FMOVIEID", $movie_id)->get();
            return view("manage.notices.create",
                ["movie" => $movie, "movie_id" => $movie_id, "notice_excels" => $notice_excels]);
        }
    }

    public function store(Request $request)
    {
        try {
            $this->validateInput($request);
        } catch (\Exception $e) {
            Session::put('message', $e->getMessage());
            return redirect()->back();
        }

        $notice = $this->createNotice($request);

        $this->createNoticeFiles($request, $notice);

        return redirect()->to("/manage/notices?movie_id=" . $request->input('movie_id'))->with("message", "创建成功");
    }

    public function edit(Request $request, $id)
    {
        $notice   = Notice::where("FID", $id)->first();
        $movie_id = $request->get("movie_id");
        return view("manage.notices.edit", compact('notice', 'movie_id', 'notice_excel'));
    }

    public function update(Request $request, $id)
    {
        try {
            $this->validateInputUpdate($request);
        } catch (\Exception $e) {
            Session::put('message', $e->getMessage());
            return redirect()->back();
        }

        $current    = date('Y-m-d H:i:s', time());
        $notice     = Notice::where("FID", $id)->orderby("FID", "DESC")->first();
        $noticeFile = $notice->excels();
        foreach ($request->input("new_file_url") as $key => $newNoticeFileUrl) {
            if ($newNoticeFileUrl) {
                if (isset($noticeFile[$key + 1])) {
                    $noticeFileName = $notice->excelinfos()[$key]->FFILENAME;
                    //更新通告单文件的url,文件名
                    DB::table('t_biz_noticeexcelsinfo')->where([
                        'FNOTICEEXCELID' => $notice->FID,
                        'FNUMBER'        => $key + 1
                    ])->update([
                        'FFILENAME' => $request->input('new_file_name')[$key],
                        'FFILEADD'  => $newNoticeFileUrl,
                        'FEDITDATE' => $current,
                        'custom_group_name' => $request->input('groupName')[$key]
                    ]);

                    $noticeexcelsid = DB::table('t_biz_noticeexcelsinfo')
                                        ->where('FNOTICEEXCELID', $notice->FID)
                                        ->where('FNUMBER', $key + 1)
                                        ->first();
                    $message        = Message::where('notice_file_id', $noticeexcelsid->FID)->where('type',
                        'NOTICE')->orderby("is_undo", "asc")->first();
                    if ($message) {
                        $message->update(['is_undo' => 1]);
                        $message->receivers()->delete();
                    }
                    $this->createNoticeRecord($request, $notice, $current, $noticeFileName, $noticeexcelsid);

                } else {
                    $notice_excel                 = new NoticeExcel;
                    $notice_excel->FID            = NoticeExcel::max("fid") + 1;
                    $notice_excel->FNOTICEEXCELID = $notice->FID;
                    $notice_excel->FFILENAME      = $request->input('new_file_name')[$key];
                    $notice_excel->FFILEADD       = $newNoticeFileUrl;
                    $notice_excel->FNEWDATE       = $current;
                    $notice_excel->FEDITDATE      = $current;
                    $notice_excel->custom_group_name     = $request->input('groupName')[$key];
                    $notice_excel->FNUMBER        = $key + 1;
                    $notice_excel->save();
                }
            }
            //
            DB::table('t_biz_noticeexcelsinfo')->where([
                'FNOTICEEXCELID' => $notice->FID,
                'FNUMBER'        => $key + 1
            ])->update([
                'custom_group_name' => $request->input('groupName')[$key]
            ]);
        }
        Notice::where('FID', $notice->FID)->update(['FEDITDATE' => $current]);

        return redirect()->to("/manage/notices?movie_id=" . $request->get('movie_id'))->with("message", "修改成功");
    }

    public function show(Request $request, $id)
    {
        $notice   = Notice::where("FID", $id)->first();
        $movie_id = $notice->FMOVIEID;
        return view("manage.notices.show", compact('movie_id', 'notice'));
    }

    public function upload(Request $request)
    {
        $uploadFile = ($request->file('file'));

        $url = Picture::upload('notices' . time(), $uploadFile);

        return response()->json([
            'success' => true,
            'msg'     => '',
            'data'    => [
                'upload_file_url'  => $url,
                'upload_file_name' => $uploadFile->getClientOriginalName()
            ]
        ]);

    }

    /**
     * @param Request $request
     *
     * @throws \Exception
     */
    private function validateInput(Request $request)
    {
//        if(! in_array(strtolower($request->file('new_file_url"')->getClientOriginalExtension()),$this->allowUploadFileTypes)){
//            throw new \Exception("通告单文件格式必须为.doc,.docx,.xls,.xlsx,.pdf,.jpg,.png格式");
//        }
        if (!$request->input("FDATE")) {
            throw new \Exception('通告单日期不能为空');
        }
        $url   = $request->input('upload_notice_file_url');
        $value = '';
        foreach ($url as $key => $urlvalue) {

            $value .= $urlvalue;
        }
        if (empty($value)) {
            throw new \Exception("通告单创建必须要有文件上传");
        }

        if ($request->file('upload_notice_file_name')) {
            if (!in_array(strtolower($request->file('upload_notice_file_name')->getClientOriginalExtension()),
                $this->allowUploadFileTypes)
            ) {
                throw new \Exception("通告单文件格式必须为.doc,.docx,.xls,.xlsx,.pdf,.jpg,.png格式");
            }
        }

        $notice = Notice::where("FMOVIEID", $request->input('movie_id'))
                        ->where("FDATE", $request->input('FDATE'))
                        ->where("FNOTICEEXCELTYPE", $request->get("FNOTICEEXCELTYPE"))
                        ->first();
        if ($notice) {
            throw new \Exception('通告单文件已经存在');
        }
    }

    private function validateInputUpdate(Request $request)
    {
        if (!$request->input("FDATE")) {
            throw new \Exception('通告单日期不能为空');
        }

        if ($request->file('new_file_name')) {
            if (!in_array(strtolower($request->file('new_file_name')->getClientOriginalExtension()),
                $this->allowUploadFileTypes)
            ) {
                throw new \Exception("通告单文件格式必须为.doc,.docx,.xls,.xlsx,.pdf,.jpg,.png格式");
            }
        };


    }

    /**
     * @param Request $request
     * @param         $noticeDate
     * @param         $current
     *
     * @return Notice
     */
    private function createNotice(Request $request)
    {
        $notice                   = new Notice;
        $notice->FID              = Notice::max("FID") + 1;
        $notice->FNOTICEEXCELTYPE = $request->input("FNOTICEEXCELTYPE");
        $notice->FDATE            = $request->input('FDATE');
        $notice->FNEWDATE         = date('Y-m-d H:i:s', time());
        $notice->FEDITDATE        = date('Y-m-d H:i:s', time());
        $notice->FNAME            = $request->input('FDATE');
        $notice->FMOVIEID         = $request->input("movie_id");
        $notice->save();
        return $notice;
    }

    /**
     * @param $files
     * @param $notice
     * @param $current
     */
    private function createNoticeFiles(Request $request, $notice)
    {
        foreach ($request->input('upload_notice_file_url') as $key => $file) {
            if ($file) {
                $notice_excel                 = new NoticeExcel;
                $notice_excel->FID            = NoticeExcel::max("FID") + 1;
                $notice_excel->FNOTICEEXCELID = $notice->FID;
                $notice_excel->FFILENAME      = $request->input('upload_notice_file_name')[$key];
                $notice_excel->FFILEADD       = $file;
                $notice_excel->custom_group_name     = $request->input('groupName')[$key];
                $notice_excel->FNEWDATE       = date('Y-m-d H:i:s', time());;
                $notice_excel->FEDITDATE      = date('Y-m-d H:i:s', time());;
                $notice_excel->FNUMBER        = $key + 1;
                $notice_excel->save();
            }
        }
    }

    private function createNoticeRecord(Request $request, $notice, $current, $noticeFileName, $noticeexcelsid)
    {
        $user                              = User::find($request->get("user_id"));
        $notice_record                     = new NoticeRecord;
        $notice_record->id                 = NoticeRecord::max('id') + 1;
        $notice_record->movie_id           = $notice->FMOVIEID;
        $notice_record->user_id            = $user->FID;
        $notice_record->notice_id          = $notice->FID;
        $notice_record->notice_file_id     = $noticeexcelsid->FID;
        $notice_record->original_file_name = $noticeFileName;
        $notice_record->new_file_name      = $noticeexcelsid->FFILENAME;
        $notice_record->editor             = $user->FNAME;
        $notice_record->created_at         = $current;
        $notice_record->updated_at         = $current;
        $notice_record->save();
    }
}



