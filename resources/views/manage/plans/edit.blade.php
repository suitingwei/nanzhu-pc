@extends('layouts.manage')


@section('dropzone_css')
    <link href="/assets/manage/vendor/dropzone/dropzone.min.css" rel="stylesheet">
@endsection


@section('content')
    <style>
        .form-group .col-md-5 {
            display: block;
        }
        .form-group .col-md-5 .form-control {
            width: 100%;
        }
    </style>
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">
                编辑参考大计划
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
                    编辑参考大计划
                </div><!-- /.panel-heading -->

                <div class="panel-body">

                    <form action="/manage/plans/{{$plan->id}}" class="form-horizontal form-row-seperated" method="POST" enctype='multipart/form-data'>
                        {{ method_field('PUT') }}
                        <input type="hidden" name="movie_id" value="{{$movie_id}}">
                        <input type="hidden" name="user_id" value="{{request()->session()->get('user_id')}}">
                        <div class="form-body">
                            <div class="form-group">
                                <label class="col-md-5">
                                    <input class="form-control" type="text" maxlength="20"  name="title" placeholder="自定义标题名称" value="{{$plan->title}}">
                                    <input type="hidden" id="new_file_url"
                                           name="new_file_url" value="">
                                    <input id='new_file_name' type="hidden"
                                           name="new_file_name" value="">
                                </label>
                                <div class="col-md-7">
                                    <div id="dropz" class="dropzone">
                                        <div class="dz-message">
                                            将文件拖至此处或点击上传.<br/>
                                            <span class="help-block">doc，docx，xls，xlsx，pdf，png，jpg <span class="h4 text-danger">主文件名中不能带 "."</span></span>
                                        </div>
                                    </div>
                                    <br>
                                    <a class="btn btn-info btn-block" href="{{$plan->file_url}}" target="blank"> {{$plan->file_name}} </a>
                                </div>
                            </div>

                            <div class="form-actions">
                                <div class="row">
                                    <div class="col-md-offset-5 col-md-7">
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
            $("#dropz").dropzone({
                url: "/manage/plans/upload",
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
                    this.on('success',function(file,response){
                        if( response.success){
                            $("#new_file_url").val(response.data.upload_file_url);
                            $("#new_file_name").val(response.data.upload_file_name);
                            $("#submitbutton").removeAttr("disabled")
                        }
                        else{
                            alert(response.message);
                        }

                    });
                }
            });

    </script>
@endsection