@extends('layouts.manage')


@section('datatables_css')
    <link href="/assets/manage/vendor/datatables/css/datatables.bootstrap.css" rel="stylesheet">
@endsection


@section('content')
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">
                我的场记日报表
            </h1>
        </div><!-- /.col-lg-12 -->
    </div><!-- /.row -->

    <div class="row">
        <div class="col-lg-12">
            <div class="panel panel-default">

                <div class="panel-heading">
                    场记日报表列表
                </div><!-- /.panel-heading -->

                <div class="panel-body">
                    <table id="dataTablesOne" class="table table-striped table-bordered table-hover table-checkable order-column" width="100%">
                        <thead>
							<tr>
								<th>标题</th>
								<th>接收详情</th>
								<th>发送时间</th>
							</tr>
                        </thead>
                        <tbody>
                        @foreach($dailyReports as $report)
                            <tr>
                                <td><a href="/manage/reports/{{$report->id}}?movie_id={{ request()->get('movie_id') }}">{{ $report->title }}</a></td>
                                <td><a href="/manage/reports/{{ $report->id }}/receivers?movie_id={{ request()->get('movie_id') }}">{{$report->messages()->orderBy('created_at','desc')->first()->readRate()}}</a></td>
                                <td>{{ $report->messages()->orderBy('created_at','desc')->first()->created_at}}</td>
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
        $(document).ready(function () {
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
                        "previous": "上一页",
                        "next": "下一页",
                        "last": "最后一页",
                        "first": "首页"
                    }
                },
                "bStateSave": false, // save datatable state(pagination, sort, etc) in cookie.

                "lengthMenu": [
                    [15, 30, 45, -1],
                    [15, 30, 45, "所有"] // change per page values here
                ],
                "order": [
                    [2, "desc"]
                ] // set first column as a default sort by asc
            });
        });
    </script>
@endsection