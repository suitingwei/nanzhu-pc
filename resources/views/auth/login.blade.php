<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>南竹通告单 | 娱乐行业从此不一样</title>
<!--[if IE]><meta http-equiv='X-UA-Compatible' content='IE=edge,chrome=1'><![endif]-->
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no"/>
<meta name="format-detection" content="telephone=no" />
<link rel="stylesheet" href="/assets/manage/static/css/login.css">
<!--[if lt IE 9]>
<script src="/assets/manage/static/js/low/html5shiv.js"></script>
<script src="/assets/manage/static/js/low/respond.min.js"></script>
<![endif]-->
<script src="/assets/manage/static/js/low/modernizr.custom.min.js"></script>
<link rel="shortcut icon" href="/assets/manage/assets/favicon.ico" />
</head>
<body>
<div class="login-container">
	<div class="wrapper">
		<div class="container">
			<h1>南竹通告单</h1>
			<form class="form" action="/login" method="post">
				<div class="div-phone">
					<input type="text" id="phone" name="phone" maxlength="11" placeholder="请输入手机号码">
				</div>
				<div class="ipt-wrap">
					<input type="text" name="code" id="code" placeholder="输入验证码">

					<div class="mob-code">
						<input class="code-btn" type="button" id="send_veriry_code_btn" value="获取验证码">
					</div>
				</div>
				<button type="submit" id="login-button">登录</button>
				@if($request->session()->has("message"))
				<div class="tips">{{$request->session()->get("message")}}</div>
					<script> alert('{{Session::get("message")}}');  </script>
					{{Session::forget('message')}}
				@endif
			</form>
			<div class="tips">
				提示：如果浏览器不关闭，您的登录验证码有效时长为24小时，每天的短信验证码最多发送10次（含手机端）
			</div>
		</div>
		<ul class="bg-bubbles">
			<li></li><li></li><li></li><li></li><li></li><li></li><li></li><li></li><li></li><li></li>
		</ul>
	</div>
</div>
<script src="/assets/manage/static/js/jquery/jquery-1.11.3.min.js"></script>
<script src="/assets/manage/static/js/jquery/jquery.cookie.js"></script>
<script src="/assets/manage/static/js/low/jquery.placeholder.min.js"></script>
<script>
jQuery(document).ready(function ($) {

	//兼容ie placeholder
	$('input, textarea').placeholder();

	/*防刷新：检测是否存在cookie*/
    if($.cookie("captcha")){
        var count = $.cookie("captcha");
        var btn = $('#send_veriry_code_btn');
        btn.val("重新获取(" + count + ")").attr('disabled',true).css('cursor','not-allowed');
        var resend = setInterval(function(){
            count--;
            if (count > 0){
                btn.val("重新获取(" + count + ")").attr('disabled',true).css('cursor','not-allowed');
                $.cookie("captcha", count, {path: '/', expires: (1/86400)*count});
            }else {
                clearInterval(resend);
                btn.val("获取验证码").removeClass('disabled').removeAttr('disabled style');
            }
        }, 1000);
    }

    /*发送短信验证*/
    $('#send_veriry_code_btn').click(function(){

    	var phonenum = $("#phone").val();
	    var myreg = /^\d{11}$/;

	    if(!myreg.test(phonenum)){
	        $('.div-phone').append('<div class="tips">请输入有效的手机号码！</div>');
	        return false;
	    }

	    if(myreg.test(phonenum)){
	    	$('.div-phone .tips').remove();

	        var btn = $(this);
	        var count = 60;
	        var resend = setInterval(function(){
	            count--;
	            if (count > 0){
	                btn.val("重新获取(" + count + ")");
	                $.cookie("captcha", count, {path: '/', expires: (1/86400)*count});
	            }else {
	                clearInterval(resend);
	                btn.val("获取验证码").removeAttr('disabled style');
	            }
	        }, 1000);
	        btn.attr('disabled',true).css('cursor','not-allowed');

	        $.get( "https://apiv2.nanzhuxinyu.com/api/account/verify_code?phone="+phonenum, function( data ) {
				alert(data.msg);
			});
       	}
    });

});
</script>
</body>
</html>
