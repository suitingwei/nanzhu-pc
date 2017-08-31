@extends('layouts.manage')


@section('content')
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">
                剧本扉页详情
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
                    剧本扉页详情
                </div><!-- /.panel-heading -->

                <div class="panel-body">
                    <div class="blog-post">
                        <h2 class="blog-post-title">{{$page->title}}</h2>
                        <p class="blog-post-meta">
                            <i class="fa fa-calendar text-info"></i>
                            <small class="text-emuted"> {{$page->created_at}} </small>
                            &nbsp;&nbsp;&nbsp;
                            <i class="fa fa-user text-info"></i>
                            <small class="text-muted">{{$gname}}/{{$from}}</small>
                            &nbsp;&nbsp;&nbsp;
                            @if($page->is_undo ==1 )
                                <span class="label label-sm label-danger">已撤销</span>
                            @endif
                        </p>
                        <hr>
                        @foreach($page->pictures() as $pic)
                            @if(App\Models\Picture::is_from_ios($pic))
                                <img src="{{$pic}}">
                            @else
                                <img src="{{App\Models\Picture::convert_pic($pic)}}@500w_1l_80Q_1pr">
                            @endif
                        @endforeach
                        {!! GrahamCampbell\Markdown\Facades\Markdown::convertToHtml($page->content) !!}

                        @if($page->files()->count() > 0)
                            @foreach($page->files as $file)
                                <a class="btn btn-info" href="{{ $file->file_url }}">{{ $file->file_name}}</a>
                            @endforeach
                        @endif
                    </div>
                    <hr>
                    <div class="col-md-offset-5">
                        <a href="javascript:history.go(-1)" class="btn btn-lg btn-default">
                            <i class="fa fa-angle-double-left"></i> 返回
                        </a>
                    </div>
                </div><!-- /.panel-body -->

            </div><!-- /.panel -->
        </div><!-- /.col-lg-12 -->
    </div><!-- /.row -->
@endsection
