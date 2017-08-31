@extends('layouts.manage')


@section('dropzone_css')
    <link href="/assets/manage/vendor/dropzone/dropzone.min.css" rel="stylesheet">
@endsection


@section('content')
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">
                编辑通告单
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
                    编辑通告单
                </div><!-- /.panel-heading -->

                <div class="panel-body">

                    <form action="/manage/notices/{{$notice->FID}}" class="form-horizontal form-row-seperated" method="POST" enctype='multipart/form-data'>
                        {{ method_field('PUT') }}
                        <input type="hidden" name="movie_id" value="{{$movie_id}}">
                        <input type="hidden" name="user_id" value="{{request()->session()->get('user_id')}}">
                        <div class="form-body">
                            <div class="form-group">
                                <label class="control-label col-md-4">通告单日期</label>
                                <div class="col-md-8">
                                    <div class="input-group date">
                                        <input type="text" name="" class="form-control" value="{{$notice->FDATE}}" disabled readonly>
                                        <input type="hidden" name="FDATE" value="{{$notice->FDATE}}">
                                        <span class="input-group-btn">
                                            <button class="btn default" type="button" disabled>
												<i class="fa fa-calendar"></i>
											</button>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-4">通告单类型</label>
                                <div class="col-md-8">
                                    <select class="bs-select form-control" name="FNOTICEEXCELTYPE" disabled>
                                        <option @if($notice->FNOTICEEXCELTYPE == 10) selected @endif value="10">每日通告单</option>
                                        <option @if($notice->FNOTICEEXCELTYPE == 20) selected @endif value="20">预备通告单</option>
                                    </select>
                                </div>
                            </div>

                            <?php $arr = ["A", "B", "C", "D", "E"] ?>

                            <?php $excels = $notice->excels(); ?>
                            <?php $custom_group_name = $notice->custom_group_name(); ?>
                            @foreach($arr as $key=> $a )
                                <div class="form-group">
                                    <label class="col-md-4">
                                        {{ $key+1 }}. <input class="form-control" type="text" name="groupName[]" maxlength="15" @if(isset($custom_group_name[$key+1]))value="{{$custom_group_name[$key+1]}}" @endif placeholder="例:文戏组或张导组等">
                                        <span class="help-block text-danger">如不填写x则默认为"{{ $a }}组"</span>
                                        <input type="hidden" id="new_file_url{{$key+1}}"
                                               name="new_file_url[]" value="">
                                        <input id='new_file_name{{$key+1}}' type="hidden"
                                               name="new_file_name[]" value="">
                                    </label>
                                    <div class="col-md-8">
                                        <div id="dropz{{$key+1}}" class="dropzone">
                                            <div class="dz-message">
                                                将文件拖至此处或点击上传.<br/>
                                                <span class="help-block">doc，docx，xls，xlsx，pdf，png，jpg <span class="h4 text-danger">主文件名中不能带 "."</span></span>
                                            </div>
                                        </div>
                                        <br>
                                        @if(isset($excels[$key+1]))
                                            <a class="btn btn-info btn-block" href="{{$excels[$key+1]}}" target="blank"> {{$excels[$key+1]}} </a>
                                        @endif
                                    </div>
                                </div>
                            @endforeach

                            <div class="form-actions">
                                <div class="row">
                                    <div class="col-md-offset-4 col-md-8">
                                        <button id="submitbutton" type="submit" class="btn btn-success btn-lg">
                                            <i class="fa fa-check"></i> 保存
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                </div><!-- /.panel-body -->

            </div><!-- /.panel -->
        </div><!-- /.col-lg-12 -->
    </div><!-- /.row -->
@endsection


@section('dropzone_js')
    <script src="/assets/manage/vendor/dropzone/dropzone.min.js"></script>
    <script>
        for(var i = 1 ;i<=5;i ++){
            $("#dropz"+i).dropzone({
                url: "/manage/notices/upload",
                maxFiles: 1,
                maxFilesize: 5,
                acceptedFiles: ".doc,.docx,.xls,.xlsx,.pdf,.jpg,.png",
                addRemoveLinks: true,

                init: function () {
                    this.on("addedfile", function (file) {
                        if(file.name.split('.').length-1!=1){
                            alert('您上传的文档主文件名中含有英文标点“.”请修改后再次上传，否则手机上无法正常打开。例如“12.10”改成“12月10日”。谢谢您的理解和配合！');
                            this.removeFile(file);
                        }
                        //如果后缀名不符合需求,给用户提示
                        var suffixName=file.name.split('.')[1].toLowerCase();
                        var suffixNames=["xls","xlsx","pdf","png","jpg","doc","docx"];
                        if(jQuery.inArray(suffixName,suffixNames)<0){
                            alert('请选择文件格式为： doc,docx,xls,xlsx,pdf,png,jpg 的文件上传');
                            this.removeFile(file);
                        }
                        // Create the remove button
                        var removeButton = Dropzone.createElement("<a href='javascript:;'' class='btn-tgddel btn btn-danger btn-sm btn-block' data-dz-remove>删除</a>");

                        // Capture the Dropzone instance as closure.
                        var _this = this;

                        // Listen to the click event
                        removeButton.addEventListener("click", function (e) {
                            // Make sure the button click doesn't submit the form:
                            e.preventDefault();
                            e.stopPropagation();

                            // Remove the file preview.
                            _this.removeFile(file);
                            // If you want to the delete the file on the server as well,
                            // you can do the AJAX request here.
                        });

                        // Add the button to the file preview element.
                        file.previewElement.appendChild(removeButton);
                    });

                    this.on("processing",function (file) {
                        $("#submitbutton").attr("disabled","true")
                    })
                    this.number = i;
                    this.on('success',function(file,response){
                        if( response.success){
                            $("#new_file_url" + this.number).val(response.data.upload_file_url);
                            $("#new_file_name" + this.number).val(response.data.upload_file_name);
                            $("#submitbutton").removeAttr("disabled")
                        }
                        else{
                            alert(response.message);
                        }

                    });
                }
            });
        }
    </script>
@endsection