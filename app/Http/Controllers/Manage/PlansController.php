<?php

namespace App\Http\Controllers\Manage;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Movie;
use App\Models\NoticeRecord;
use App\Models\Picture;
use App\Models\ReferencePlan;
use App\User;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Session;

class PlansController extends Controller
{
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
            $plans = ReferencePlan::where("movie_id", $movieId)->orderby("created_at", "desc")->get();
            return view('manage.plans.index', [
                "plans"  => $plans,
                "movie_id" => $movieId,
            ]);
        }
    }
    public function store(Request $request)
    {
        try {
            $this->validateInputCreate($request);
        } catch (\Exception $e) {
            Session::put('message', $e->getMessage());
            return redirect()->back();
        }
        $this->createPlan($request);
        return redirect()->to("/manage/plans?movie_id=" . $request->input('movie_id'))->with("message", "创建成功");



    }


    public function create(Request $request)
    {
        $movie_id = $request->get("movie_id");
        if ($movie_id) {
            $movie         = Movie::find($movie_id);
            return view("manage.plans.create",
                ["movie" => $movie, "movie_id" => $movie_id]);
        }
    }

    private function createPlan(Request $request)
    {
        foreach ($request->input('upload_plan_file_url') as $key => $file) {
          if($file){
            $reference_plan = new ReferencePlan;
            $reference_plan->id         = ReferencePlan::max("id") + 1;
            $reference_plan->title      = $request->input("title")[$key];
            $reference_plan->movie_id   = $request->input("movie_id");
            $reference_plan->file_url   = $file;
            $reference_plan->file_name  = $request->input('upload_plan_file_name')[$key];
            $reference_plan->file_path  = $file;
            $reference_plan->creator_id = $request->input('user_id');
            $reference_plan->save();
          }
        }

    }
        public function edit(Request $request, $id)
    {
        $plan   = ReferencePlan::where("id", $id)->first();
        $movie_id = $request->get("movie_id");
        return view("manage.plans.edit", compact('plan', 'movie_id'));
    }

    public function update(Request $request, $id)
    {
        $current = date('Y-m-d H:i:s', time());
        $plan = ReferencePlan::find($id);
        $newPlanFileUrl = $request->input('new_file_url');
        if(!$newPlanFileUrl) {
            if($request->input('title') == $plan->title) {
                $plan->update(['updated_at => $current']);
                $this->updateMessages($id);
                $this->createPlanRecord($request,$plan,$current);
                return redirect()->to("/manage/plans?movie_id=" . $request->get('movie_id'))->with("message", "修改成功");
            }
                $plan->update([
                    'title'      => $request->input('title'),
                    'creator_id' => $request->input('user_id'),
                    'updated_at' => $current
                ]);
            $this->updateMessages($id);
            $this->createPlanRecord($request,$plan,$current);
            return redirect()->to("/manage/plans?movie_id=" . $request->get('movie_id'))->with("message", "修改成功");
            }
        $plan->update([
                'title'      => $request->input('title'),
                'file_url'   => $request->input('new_file_url'),
                'file_name'  => $request->input('new_file_name'),
                'file_path'  => $request->input('new_file_url'),
                'creator_id' => $request->input('user_id'),
                'updated_at' => $current
            ]);
        $this->updateMessages($id);
        $this->createPlanRecord($request,$plan,$current);
        return redirect()->to("/manage/plans?movie_id=" . $request->get('movie_id'))->with("message", "修改成功");
    }

    public function show(Request $request, $id)
    {
        $plan   = ReferencePlan::where("id", $id)->first();
        $movie_id = $plan->movie_id;
        return view("manage.plans.show", compact('movie_id', 'plan'));
    }

    public function upload(Request $request)
    {
        $uploadFile = ($request->file('file'));

        $url = Picture::upload('plans' . time(), $uploadFile);

        return response()->json([
            'success' => true,
            'msg'     => '',
            'data'    => [
                'upload_file_url'  => $url,
                'upload_file_name' => $uploadFile->getClientOriginalName()
            ]
        ]);
    }
        private function createPlanRecord(Request $request,$plan,$current)
    {
        $user = User::find($request->get("user_id"));
        $notice_record = new NoticeRecord;
        $notice_record->id = NoticeRecord::max('id')+1;
        $notice_record->movie_id = $plan->movie_id;
        $notice_record->user_id = $user->FID;
        $notice_record->notice_id = $plan->id;
        $notice_record->notice_file_id = $plan->id;
        $notice_record->original_file_name = $plan->file_name;
        $notice_record->new_file_name = $request->input('new_file_name');
        $notice_record->editor = $user->FNAME;
        $notice_record->created_at = $current;
        $notice_record->updated_at = $current;
        $notice_record->save();

    }
    private function updateMessages($id)
    {
        $message = Message::where('plan_id',$id)->where('type','PLAN')->orderby('is_undo','asc')->first();
        if($message) {
            $message->update(['is_undo' => 1]);
            $message->receivers()->delete();
        }
    }
    private function validateInputCreate(Request $request)
    {
        foreach ($request->input('upload_plan_file_name') as $key => $file) {
            if ($request->input('title')[$key]) {
                if (!$file) {
                    throw new \Exception('请上传与标题对应的文件');
                }
            }
            if ($file) {
                if (!$request->input('title')[$key]) {
                    throw new \Exception('请填写与文件对应的标题');
                }
            }
        }
    }



}
