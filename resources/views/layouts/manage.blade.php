<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>南竹通告单</title>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
<meta name="format-detection" content="telephone=no">
<link rel="shortcut icon" href="/assets/manage/assets/favicon.ico"/>
<link href="/assets/manage/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
<link href="/assets/manage/vendor/font-awesome/css/font-awesome.min.css" rel="stylesheet">
@yield('datatables_css')
@yield('datepicker_css')
@yield('dropzone_css')
<link href="/assets/manage/dist/css/sb-admin-2.css" rel="stylesheet">
<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
<!--[if lt IE 9]>
<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
<script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
<![endif]-->
</head>
<body>

@if(Session::has("message"))
    <div class="toast-top-center">
        <div class="toast toast-success">
            <div class="toast-message">{{Session::get("message")}}</div>
        </div>
    </div><!-- tips -->
@endif
{{Session::forget("message")}}

<div id="wrapper">

    <nav class="navbar navbar-default navbar-static-top" role="navigation" style="margin-bottom: 0">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a class="navbar-brand"><img src="/assets/manage/dist/img/logo.png" alt="南竹通告单"></a>
        </div><!-- /end -->

        <div class="collapse navbar-collapse">
            <ul class="nav navbar-nav navbar-right">
                @if(Session::get('movies'))
                    @foreach(Session::get("movies") as $key => $movie)
                        <li class=" @if($movie->FID==$movie_id)  active @else  divider-vertical @endif ">
                            <a href="/manage/notices?movie_id={{$movie->FID}}">
                                <i class="glyphicon glyphicon-film"></i>
                                {{$movie->FNAME}}
                            </a>
                        </li>
                    @endforeach
                @endif
                <li class="divider-vertical"></li>

                <li>
                    <a href="/logout">
                        <i class="fa fa-sign-out fa-fw"></i>
                        退出
                    </a>
                </li><!-- end -->
            </ul>
        </div><!-- /end -->

        <div class="navbar-default sidebar" role="navigation">
            <div class="sidebar-nav navbar-collapse">
                <ul class="nav" id="side-menu">

                    <li class="sidebar-im">
                        <a href="https://apiv2.nanzhuxinyu.com/im?u={{encrypt(request()->session()->get('user_id'))}}"
                           rel="external" class="btn btn-primary btn-block">
                            <i class="fa fa-comments"></i>
                            南竹通告单网页版聊天
                        </a>
                    </li>

                    @if(\App\User::find(Session::get('user_id'))->isTongChouInMovie($movie_id))
                        <li>
                            <a data-toggle="collapse" data-target="#colOne" aria-expanded="true" aria-controls="colOne"><i
                                        class="fa fa-calendar fa-fw"></i> 通告单</a>
                            <ul id="colOne" class="nav nav-second-level collapse in">
                                <li>
                                    <a @if (request()->path() == "manage/notices") class="active"
                                       @endif href="/manage/notices?movie_id={{$movie_id}}">我的通告单</a>
                                </li>
                                <li>
                                    <a @if (request()->path() == "manage/notices/create") class="active"
                                       @endif href="/manage/notices/create?movie_id={{$movie_id}}">新建通告单</a>
                                </li>
                            </ul><!-- /.nav-second-level -->
                        </li>
                    @endif

                    <li>
                        <a data-toggle="collapse" data-target="#colTwo" aria-expanded="true" aria-controls="colTwo"><i
                                    class="fa fa-bullhorn fa-fw"></i> 剧组通知</a>
                        <ul id="colTwo" class="nav nav-second-level collapse in">
                            <li>
                                <a @if (request()->path() == "manage/messages") class="active"
                                   @endif href="/manage/messages?movie_id={{$movie_id}}">我的剧组通知</a>
                            </li>
                            <li>
                                <a @if (request()->path() == "manage/messages/create") class="active"
                                   @endif href="/manage/messages/create?movie_id={{$movie_id}}">添加剧组通知</a>
                            </li>
                        </ul><!-- /.nav-second-level -->
                    </li>

                    <li>
                        <a data-toggle="collapse" data-target="#colThree" aria-expanded="true" aria-controls="colThree">
                            <i class="fa fa-file-image-o fa-fw"></i> 剧本扉页</a>
                        <ul id="colThree" class="nav nav-second-level collapse in">
                            <li>
                                <a @if (request()->path() == "manage/pages") class="active"
                                   @endif href="/manage/pages?movie_id={{$movie_id}}">我的剧本扉页</a>
                            </li>
                            <li>
                                <a @if (request()->path() == "manage/pages/create") class="active"
                                   @endif href="/manage/pages/create?movie_id={{$movie_id}}">添加剧本扉页</a>
                            </li>
                        </ul><!-- /.nav-second-level -->
                    </li>
                    <li>
                        <a data-toggle="collapse" data-target="#colFour" aria-expanded="true" aria-controls="colFour">
                            <i class="fa fa-file-image-o fa-fw"></i> 车辆管理</a>
                        <ul id="colFour" class="nav nav-second-level collapse in">
                            <li>
                                <a @if (request()->path() == "manage/vehicle") class="active"
                                   @endif href="/manage/vehicle?movie_id={{$movie_id}}">当前位置</a>
                            </li>
                        </ul><!-- /.nav-second-level -->
                    </li>

                    @if(\App\User::find(Session::get('user_id'))->isTongChouInMovie($movie_id))
                        <li>
                            <a data-toggle="collapse" data-target="#colFour" aria-expanded="true"
                               aria-controls="colFour">
                                <i class="fa fa-bullhorn fa-file-text-o"></i> 参考大计划</a>
                            <ul id="colFour" class="nav nav-second-level collapse in">
                                <li>
                                    <a @if (request()->path() == "manage/plans") class="active"
                                       @endif href="/manage/plans?movie_id={{$movie_id}}">我的参考大计划</a>
                                </li>
                                <li>
                                    <a @if (request()->path() == "manage/plans/create") class="active"
                                       @endif href="/manage/plans/create?movie_id={{$movie_id}}">添加参考大计划</a>
                                </li>
                            </ul><!-- /.nav-second-level -->
                        </li>
                    @endif


                    @if(\App\User::find(Session::get('user_id'))->hadAssignedDailyReportPower($movie_id))
                    <li>
                        <a data-toggle="collapse" data-target="#colFive" aria-expanded="true" aria-controls="colFive">
                            <i class="fa fa-edit fa-fw"></i> 场记日报表</a>
                        <ul id="colFive" class="nav nav-second-level collapse in">
                            <li>
                                <a @if (request()->path() == "manage/reports") class="active"
                                   @endif href="/manage/reports?movie_id={{$movie_id}}">我的场记日报表</a>
                            </li>
                        </ul><!-- /.nav-second-level -->
                    </li>
                    @endif

                </ul>
            </div><!-- /end -->
        </div><!-- /end -->
    </nav><!-- /end -->

    <div id="page-wrapper">
        @yield('content')
    </div><!-- /end -->

</div><!-- /end -->

<script src="/assets/manage/vendor/jquery/jquery.min.js"></script>
<script src="/assets/manage/vendor/bootstrap/js/bootstrap.min.js"></script>
@yield('datatables_js')
@yield('datepicker_js')
@yield('dropzone_js')
<script>
jQuery(document).ready(function () {

    $('.toast-top-center').delay(2000).fadeOut(1000);

    var sFeatures = "height=600, width=920, top=0, left=0, toolbar=no, menubar=no, scrollbars=no, resizable=no, location=no, status=no";
    jQuery('a[rel="external"]').click(function () {
        window.open(jQuery(this).attr('href'), '3km', sFeatures);
        return false;
    });
    jQuery("a[rel=external]").attr('target', '_blank');

});
</script>
</body>
</html>