@extends('layouts.manage')


@section('datatables_css')
    <link href="/assets/manage/vendor/datatables/css/datatables.bootstrap.css" rel="stylesheet">
@endsection


@section('content')
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">
                接收详情
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
                    接收详情列表
                </div><!-- /.panel-heading -->

                <div class="panel-body">
                    <table id="dataTablesOne" class="table table-striped table-bordered table-hover table-checkable order-column" width="100%">
                        <thead>
                            <tr>
                                <th>组别</th>
                                <th>职位</th>
                                <th>姓名</th>
                                <th>接收时间</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        function cmp($a, $b)
                        {
                            return strcmp($a['groupid'], $b['groupid']);
                        }
                        usort($un_receivers, "cmp");
                        ?>

                        @foreach($un_receivers as $receiver)
                            <tr>
                                <td>
                                    <?php $unReceiveUser = App\User::find($receiver['uid']) ?>
                                    <?php $firstJoinGroup = $unReceiveUser->groupsInMovie($movieId)->first(); ?>
                                    {{--@if($firstJoinGroup){{ $firstJoinGroup->FNAME}}  @endif--}}
                                    {{ $unReceiveUser->groupNamesInMovie($movieId) }}
                                </td>
                                <td>
                                    {{$receiver['job']}}
                                </td>
                                <td>
                                    <?php $phones = \DB::table("t_biz_sparephone")->where("FGROUPUSERID",
                                            $receiver['group_user_id'])->get();?>
                                    <div class="dropdown">
                                            <a id="dLabel" @if($unReceiveUser->sharePhonesInMovie($movieId)) href="javascript:;" @endif data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                {{$receiver['username']}}
                                            </a>
                                            <ul class="dropdown-menu" role="menu" aria-labelledby="dLabel">
                                                @foreach($unReceiveUser->sharePhonesInMovie($movieId)as $phone)
                                                    @if($phone->FChecked)
                                                        <li>{{$phone->FPHONE}}</li>
                                                        <li role="presentation" class="divider"></li>
                                                    @endif
                                                @endforeach
                                            </ul>

                                    </div>
                                </td>
                                <td>
                                    @if($receiver['created_at'] != $receiver['updated_at'])
                                        {{date("Y-m-d H:i:s",strtotime($receiver['updated_at']))}}
                                    @else
                                        <span class="text-muted">未读</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        @foreach($receivers as $receiver)
                            <tr>
                                <td>
                                    <?php $user = App\User::find($receiver['uid']) ?>
                                    <?php $firstJoinGroup = $user->groupsInMovie($movieId)->first(); ?>
                                    {{--@if($firstJoinGroup){{ $firstJoinGroup->FNAME}}  @endif--}}
                                    {{ $user->groupNamesInMovie($movieId) }}
                                </td>
                                <td>
                                    {{$receiver['job']}}
                                </td>
                                <td>
                                    <?php $phones = \DB::table("t_biz_sparephone")->where("FGROUPUSERID",
                                            $receiver['group_user_id'])->get();?>
                                    <div class="dropdown">
                                        @if($user->isSharePhonesInMovieOpened($movieId))
                                            <a id="dLabel" href="javascript:;" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                {{$receiver['username']}}
                                            </a>
                                            <ul class="dropdown-menu" role="menu" aria-labelledby="dLabel">
                                                @foreach($user->sharePhonesInMovie($movieId)as $phone)
                                                    @if($phone->FChecked)
                                                        <li>{{$phone->FPHONE}}</li>
                                                        <li role="presentation" class="divider"></li>
                                                    @endif
                                                @endforeach
                                            </ul>
                                        @else
                                            <a id="dLabel" href="javascript:;" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                {{$receiver['username']}}
                                            </a>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if($receiver['created_at'] != $receiver['updated_at'])
                                        {{date("Y-m-d H:i:s",strtotime($receiver['updated_at']))}}
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div><!-- /.panel-body -->

            </div><!-- /.panel -->
        </div><!-- /.col-lg-12 -->
    </div><!-- /.row -->
@endsection


@section('datatables_js')
    <script src="/assets/manage/vendor/datatables/js/jquery.datatables.min.js"></script>
    <script src="/assets/manage/vendor/datatables/js/datatables.bootstrap.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#dataTablesOne').dataTable({
                "responsive": true,
                "language": {
                    "aria": {
                        "sortAscending": ": 激活升序排列",
                        "sortDescending": ": 激活降序排列"
                    },
                    "emptyTable": "对不起，您还没有添加数据",
                    "info": "已显示 _END_ / _TOTAL_ 条数据",
                    "infoEmpty": "对不起，没有搜索到",
                    "infoFiltered": "(已搜索全部 _MAX_ 条数据)",
                    "search": "搜索:",
                    "lengthMenu": "_MENU_ 每页显示条数",
                    "zeroRecords": "对不起，没有符合条件的数据",
                    "paginate": {
                        "previous":"上一页",
                        "next": "下一页",
                        "last": "最后一页",
                        "first": "首页"
                    }
                },
                "bStateSave": true, // save datatable state(pagination, sort, etc) in cookie.
                "lengthMenu": [
                    [15, 30, 45, -1],
                    [15, 30, 45, "所有"] // change per page values here
                ],
                "order": [
                    [0, "asc"]
                ] // set first column as a default sort by asc
            });
        });
    </script>
@endsection