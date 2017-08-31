@extends('layouts.manage')


@section('content')
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">
                通告单详情
                <span class="pull-right">
                    <a href="/manage/notices/" class="btn btn-default"><i class="fa fa-angle-double-left"></i> 返回</a>
                </span>
            </h1>
        </div><!-- /.col-lg-12 -->
    </div><!-- /.row -->

    <div class="row">
        <div class="col-lg-12">
            <div class="panel panel-default">

                <div class="panel-heading">
                    通告单详情
                </div><!-- /.panel-heading -->

                <div class="panel-body">
                    <div class="form-horizontal form-row-seperated">
                        <div class="form-body">
                            <div class="form-group">
                                <label class="control-label col-md-3">通告单日期</label>
                                <div class="col-md-8">
                                    <label class="control-label">{{$notice->FNAME}}</label>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-3">通告单类型</label>
                                <div class="col-md-8">
                                    <label class="control-label">{{$notice->type_desc()}}</label>
                                </div>
                            </div>
                            <?php $arr = ['A','B','C','D','E']?>
                            @foreach($notice->excelinfos() as $key => $excel)
                            <div class="form-group">
                                <label class="control-label col-md-3">{{$key+1}}.@if($excel->custom_group_name){{$excel->custom_group_name}}@else{{$arr[$excel->FNUMBER-1]}}组@endif</label>
                                <div class="col-md-8">
                                    <a class="btn btn-link" target="blank" href="{{$excel->FFILEADD}}">
                                        <i class="fa fa-file fileinput-exists"></i>
                                        {{$excel->FFILENAME}}
                                    </a>
                                    @if($notice->excel_is_send($excel->FID))
                                        <span class="label label-sm label-success"> 已发送 </span>
                                    @else
                                        <span class="label label-sm label-danger"> 未发送 </span>
                                    @endif
                                </div>
                            </div>
                            @endforeach

                            <div class="form-actions">
                                <div class="row">
                                    <div class="col-md-offset-3 col-md-9">
                                        <a href="/manage/notices/{{$notice->FID}}/edit?movie_id={{$movie_id}}" class="btn btn-lg btn-success">
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
