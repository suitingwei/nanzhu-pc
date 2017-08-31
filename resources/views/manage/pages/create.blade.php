@extends('layouts.manage')


@section('dropzone_css')
    <link href="/assets/manage/vendor/dropzone/dropzone.min.css" rel="stylesheet">
@endsection


@section('content')
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">
                添加剧本扉页
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
                    添加剧本扉页
                </div><!-- /.panel-heading -->

                <div class="panel-body">

                    <form id="submitForm" action="/manage/messages" class="form-horizontal form-row-seperated"
                          method="POST" enctype='multipart/form-data'>
                        <div class="form-body">
                            <div class="form-group">
                                <label class="control-label col-md-1">标题</label>
                                <input type="hidden" name="movie_id" value="{{$movie_id}}">
                                <input type="hidden" name="type" value="BLOG">
                                <input type="hidden" name="user_id" value="{{request()->session()->get('user_id')}}">
                                <div class="col-md-8">
                                    <input class="form-control" id="title" type="text" name="title"
                                           placeholder="输入剧本扉页标题">
                                </div>
                                <div id="titlediv"></div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-1">内容</label>
                                <div class="col-md-8">
                                    <textarea class="form-control" id="content" rows="8" placeholder="输入内容"
                                              name="content"></textarea>
                                </div>
                                <div id="contentdiv"></div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-1">文档</label>
                                <div class="col-md-8">
                                    <div id="dropzPic" class="dropzone">
                                        <div class="dz-message">
                                            将文件拖至此处或点击上传.<br/>
                                            <span class="help-block">上传格式 {{ implode(',',$allowedUploadFileTypes) }} </span>
                                            <span class="h4 text-danger">主文件名中不能带 "."</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-actions">
                                <div class="row">
                                    <div class="col-md-offset-1 col-md-9">
                                        <button id="submitbutton" type="submit"
                                                class="btn btn-success btn-lg btn-submit">
                                            <i class="fa fa-paper-plane"></i> 发送
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
        $("#dropzPic").dropzone({
            url: "/manage/messages/upload",
            maxFiles: 9,
            maxFilesize: 5,
            acceptedFiles: "{{ implode(',',$allowedUploadFileTypesWithDot ) }}",
            addRemoveLinks: true,

            init: function () {
                this.on("addedfile", function (file) {
                    if (file.name.split('.').length - 1 != 1) {
                        alert('您上传的文档主文件名中含有英文标点“.”请修改后再次上传，否则手机上无法正常打开。例如“12.10”改成“12月10日”。谢谢您的理解和配合！');
                        this.removeFile(file);
                    }
                    //如果后缀名不符合需求,给用户提示
                    var suffixName=file.name.split('.')[1].toLowerCase();
                    var suffixNames=["xls","xlsx","pdf","png","jpg","doc","docx",'ppt','pptx'];
                    if(jQuery.inArray(suffixName,suffixNames)<0){
                        alert('请选择文件格式为： doc,docx,xls,xlsx,pdf,png,jpg,ppt,pptx 的文件上传');
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
                this.on("processing", function (file) {
                    $(".btn-submit").attr("disabled", "true")
                });
                this.on('success', function (file, response) {
                    var newImgInput = '';
                    if (response.data.is_image_type) {
                        newImgInput = "<input type=hidden name='img_url[]' value=" + response.data.uploaded_file_url + ">";
                    } else {
                        newImgInput = "<input type=hidden name='file_url[]' value=" +
                                response.data.uploaded_file_url +
                                " file_type='" + response.data.file_type +
                                " 'file_name='" + response.data.file_name + "'>";
                    }

                    $('form').append(newImgInput);
                    $(".btn-submit").removeAttr("disabled")
                });
            }
        });


        $("#title").blur(function () {
            check('title');
        });
        $("#content").blur(function () {
            check('content');
        });
        $("#submitbutton").click(function () {
            var checktitle = check('title');
            var checkcontent = check('content');
            if (checktitle == "false" || checkcontent == "false") {
                return false;
            }

            $("input[name='file_url[]']").each(function (key, value) {
                var fileUrl = $(this).val();
                var fileType = $(this).attr('file_type');
                var fileName = $(this).attr('file_name');
                $(this).val(JSON.stringify({
                    file_url: fileUrl,
                    file_type: fileType,
                    file_name: fileName
                }));
            });

        });
        $("#btnSubmit").click(function () {
            var checktitle = check('title');
            var checkcontent = check('content');
            if (checktitle == "false" || checkcontent == "false") {
                return false;
            }

            $("#submitForm").submit();
        });

        function check($checked) {
            var content = $("#" + $checked + "").val();
            if (content == "") {
                $("#" + $checked + "div").html('<span class="text-danger">该内容未填写</span>');
                return "false";
            } else {
                $("#" + $checked + "div").html('');
                return "true";
            }
        }
    </script>
@endsection