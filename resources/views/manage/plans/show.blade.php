@extends('layouts.manage')


@section('content')
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">
                参考大计划详情
                <span class="pull-right">
                    <a href="/manage/plans/" class="btn btn-default"><i class="fa fa-angle-double-left"></i> 返回</a>
                </span>
            </h1>
        </div><!-- /.col-lg-12 -->
    </div><!-- /.row -->

    <div class="row">
        <div class="col-lg-12">
            <div class="panel panel-default">

                <div class="panel-heading">
                    参考大计划详情
                </div><!-- /.panel-heading -->

                <div class="panel-body">
                    <div class="form-horizontal form-row-seperated">
                        <div class="form-body">
                            <div class="form-group">
                                <label class="control-label col-md-3">{{$plan->title}}</label>
                                <div class="col-md-8">
                                    <a class="btn btn-link" target="blank" href="{{$plan->file_url}}" download="111.jpg">
                                        <i class="fa fa-file fileinput-exists"></i>
                                        {{$plan->file_name}}
                                    </a>
                                    @if($plan->excel_is_send($plan->id))
                                        <span class="label label-sm label-success"> 已发送 </span>
                                    @else
                                        <span class="label label-sm label-danger"> 未发送 </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-actions">
                                <div class="row">
                                    <div class="col-md-offset-3 col-md-9">
                                        <a href="/manage/notices/{{$plan->id}}/edit?movie_id={{$movie_id}}" class="btn btn-lg btn-success">
                                            <i class="fa fa-edit"></i> 编辑
                                        </a>
                                        &nbsp;&nbsp;&nbsp;&nbsp;
                                        <a href="javascript:history.go(-1)" class="btn btn-lg btn-default">
                                            <i class="fa fa-angle-double-left"></i> 返回
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div><!-- /.panel-body -->

            </div><!-- /.panel -->
        </div><!-- /.col-lg-12 -->
    </div><!-- /.row -->
@endsection