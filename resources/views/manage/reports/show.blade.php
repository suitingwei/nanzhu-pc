@extends('layouts.manage')


@section('content')
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">
                场记日报表详情
                <span class="pull-right">
                    <a href="javascript:history.go(-1)" class="btn btn-default"><i class="fa fa-angle-double-left"></i> 返回</a>
                </span>
            </h1>
        </div><!-- /.col-lg-12 -->
    </div><!-- /.row -->

    <div class="row">
        <div class="col-lg-12">
            <div class="panel panel-default">

                <div class="panel-heading">
                    场记日报表详情
                </div><!-- /.panel-heading -->

                <div class="panel-body">
                    <div class="blog-post">
                        <h2 class="blog-post-title">{{$report->title}}</h2>
                        <p class="blog-post-meta">
                            <i class="fa fa-calendar text-info"></i>
                            <small class="text-emuted">{{$report->created_at}}</small>
                        </p>
						<hr>
						<div class="table-responsive">
							<table class="table table-striped table-bordered table-hover">
								<thead>
									<tr>
										<th>出发</th>
										<th>到场</th>
										<th>开机</th>
										<th>收工</th>
									</tr>
								</thead>
								<tbody>
									<tr>
										<td>{{ $report->depart_time }}</td>
										<td>{{ $report->arrive_time }}</td>
										<td>{{ $report->action_time }}</td>
										<td>{{ $report->finish_time }}</td>
									</tr>
								</tbody>
							</table>
						</div>
						{{ $report->note }}
                        @foreach($report->pictures->lists('url') as $pic)
                            <img src="{{$pic}}">
                        @endforeach
                    </div>
					<br><br><br>
                    <div class="text-right">
                        <?php $user =  App\User::find(request()->session()->get('user_id')) ?>
                        发布者：{{ $user->FNAME }}<br>
                        部门：{{ $user->groupNamesInMovie($movie_id) }}<br>
                        最后编辑时间: {{ $report->updated_at }}
                    </div>
					<hr>
                    <div class="col-md-offset-5">
                        <a href="javascript:history.go(-1)" class="btn btn-lg btn-default">
                            <i class="fa fa-angle-double-left"></i> 返回
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection