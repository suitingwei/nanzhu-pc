@extends('layouts.manage')


@section('datatables_css')
    <link href="/assets/manage/vendor/datatables/css/datatables.bootstrap.css" rel="stylesheet">
@endsection


@section('content')
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">
                车辆管理
            </h1>
        </div><!-- /.col-lg-12 -->
    </div><!-- /.row -->

    <div class="row">
        <div class="col-lg-12">
            <div class="panel panel-default">

                <div class="panel-heading">
                    当前位置
                </div><!-- /.panel-heading -->
                <div class="panel-heading" style="background:none">
                    车辆
                    <select>
                        <option>11</option>
                    </select>
                </div>
                <div class="panel-body">

                    <div id="container" style="width:100%;height:600px">
                    </div>
                    <script src="http://webapi.amap.com/maps?v=1.3&key=34b413c7f87ccf005d8eec57285ff073"></script>
                    <script src="http://webapi.amap.com/js/marker.js"></script>

                    <script src="/assets/manage/vendor//jquery/jquery.min.js"></script>
                    <script type="text/javascript">

                        window.onload=function(){
                            getnewajax();
                        };
                        function getnewajax() {
                            $.ajax({
                                type: 'get',
                                url: '/manage/ajax-user-locations',
                                data: {movie_id: {{$movie_id}} },
                                success: function (res) {
                                    var arr=res.locations;
                                    for(var i=0;i<arr.length;i++){
                                        move(arr[i].longitude,arr[i].latitude,arr[i].user_name,arr[i].user_id);
                                    }
                                }
                            });
                        }

                        var map = new AMap.Map("container", {resizeEnable: true,zoom:4});
                        var markers = [];


                        //轮循
                        var icon = new AMap.Icon({
                            image: 'http://vdata.amap.com/icons/b18/1/2.png',
                            size: new AMap.Size(24, 24)
                        });
                        var offset=new AMap.Pixel(-12, -12);
                        function move(longtitude,latitude,username,vehicle){
                            var marker = new AMap.Marker({
                                position: [longtitude,latitude],
                                offset: offset,
                                zIndex: 10,
                                title: username+vehicle,
                                map: map
                            });
                            marker.setLabel({//label默认蓝框白底左上角显示，样式className为：amap-marker-label
                                offset: new AMap.Pixel(-20, -20),//修改label相对于maker的位置
                                content: username+vehicle
                            });
                        }
                        //没10秒执行一次
                        setInterval(function(){
                            console.log(1)
                            map.remove(markers);
                            getnewajax();
                        },10000);

                    </script>
                    <script type="text/javascript" src="http://webapi.amap.com/demos/js/liteToolbar.js"></script>
                </div><!-- /.panel-body -->

            </div><!-- /.panel -->
        </div><!-- /.col-lg-12 -->
    </div><!-- /.row -->
@endsection


@section('datatables_js')

    <script>

    </script>
@endsection