@extends('layouts.manage')


@section('datatables_css')
    <link href="/assets/manage/vendor/datatables/css/datatables.bootstrap.css" rel="stylesheet">
@endsection


@section('content')
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">
                我的参考大计划
                <span class="pull-right">
                    <a href="/manage/plans/create?movie_id={{$movie_id}}" class="btn btn-success"><i class="fa fa-plus"></i> 添加参考大计划</a>
                </span>
            </h1>
        </div><!-- /.col-lg-12 -->
    </div><!-- /.row -->

    <div class="row">
        <div class="col-lg-12">
            <div class="panel panel-default">

                <div class="panel-heading">
                    参考大计划列表
                </div><!-- /.panel-heading -->

                <div class="panel-body">
                    <table id="dataTablesOne" class="table table-striped table-bordered table-hover table-checkable order-column" width="100%">
                        <thead>
                            <tr>
                                <th class="text-center">
                                    <input type="checkbox" class="group-checkable" data-set="#dataTablesOne .checkboxes" />
                                </th>
                                <th>标题</th>
                                <th>是否发送</th>
                                <th>编辑记录</th>
                                <th>编辑时间</th>
                                <th>创建时间</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody>
                        @foreach($plans as $plan)
                            <tr class="odd gradeX">
                                <td class="text-center">
                                    <input type="checkbox" class="checkboxes" value="1" /></td>
                                <td>
                                    {{$plan->title}}
                                </td>
                                <td>
                                        @if($plan->plans_is_send($plan->id))
                                            <span class="label label-sm label-success">已发送</span>&nbsp;
                                        @else
                                            <span class="label label-sm label-danger">未发送</span>
                                        @endif
                                </td>
                                <td class="center">
                                    <div class="dropdown">
                                        <a id="dLabel" href="javascript:;"  data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            编辑记录
                                        </a>
                                        <ul class="dropdown-menu" role="menu" aria-labelledby="dLabel">
                                                <?php  $records = \App\Models\NoticeRecord::where("notice_id",$plan->id)->orderby("created_at","desc")->get()->all();?>
                                           @if(count($records)>0)
                                            @foreach($records as $record)
                                                <li>{{$record->editor}}&nbsp;&nbsp;{{$record->created_at}}</li>
                                                <li role="presentation" class="divider"></li>
                                            @endforeach
                                               @elseif(count($records) == 0)
                                                <li>暂无编辑记录</li>
                                                        <li role="presentation" class="divider"></li>
                                                    @endif
                                        </ul>

                                    </div>
                                </td>
                                <td class="center">{{$plan->updated_at}}</td>
                                <td class="center">
                                    <span class="text-gray">{{$plan->created_at}}</span>
                                </td>

                                <td>
                                    <a href="/manage/plans/{{$plan->id}}/edit?movie_id={{$movie_id}}" class="btn btn-xs btn-success">
                                        <i class="fa fa-edit"></i>
                                        编辑</a>
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
                "bStateSave": false, // save datatable state(pagination, sort, etc) in cookie.
                "columnDefs": [ {
                    "targets": 0,
                    "orderable": false,
                    "searchable": false
                }],
                "lengthMenu": [
                    [15, 30, 45, -1],
                    [15, 30, 45, "所有"] // change per page values here
                ],
                "order": [
                    [5, "desc"]
                ] // set first column as a default sort by asc
            });

            var table = $('#dataTablesOne');
            table.find('.group-checkable').change(function () {
                var set = jQuery(this).attr("data-set");
                var checked = jQuery(this).is(":checked");
                jQuery(set).each(function () {
                    if (checked) {
                        $(this).prop("checked", true);
                        $(this).parents('tr').addClass("active");
                    } else {
                        $(this).prop("checked", false);
                        $(this).parents('tr').removeClass("active");
                    }
                });
            });
            table.on('change', 'tbody tr .checkboxes', function () {
                $(this).parents('tr').toggleClass("active");
            });

        });
    </script>
@endsection