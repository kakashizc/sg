<?php defined('InShopBN') or exit('Access Invalid!'); ?>
<body class="templatemo-bg-image-2">
<div class="container">
    <div class="col-md-12">
        <form class="form-horizontal templatemo-contact-form-1 login-form"
              action="index.php?act=login&op=login" method="post">
            <?php Security::getToken(); ?>
            <input type="hidden" name="form_submit" value="ok"/>
            <input name="nchash" type="hidden" value="<?php echo getNchash(); ?>"/>
            <div class="form-group">
                <div class="col-md-12">
                    <h1 class="margin-bottom-15"><?php echo $output['html_title'];?></h1></div>
            </div>
            <div class="form-group">
                <div class="col-md-12"><label class="control-label" for="name">用户名</label>
                    <div class="templatemo-input-icon-container"><I class="fa fa-user"></I>
                        <input name="username" class="form-control" id="username" type="text" placeholder="用户名"
                               value="" required></div>
                </div>
            </div>
            <div class="form-group">
                <div class="col-md-12"><LABEL class="control-label" for="email">密码</LABEL>

                    <div class="templatemo-input-icon-container"><I class="fa fa-lock"></I>

                        <input name="pwd" class="form-control" id="pwd" type="password" placeholder="密码" value="">
                    </div>
                </div>
            </div>
            <div class="form-group">
                <div class="col-md-12"><label class="control-label" for="email">验证码</label>

                    <div class="templatemo-input-icon-container"><I class="fa fa-qrcode"></I>

                        <input name="captcha" class="form-control" id="captcha" type="text"
                               placeholder="验证码" value="">
                        <img class="verifyimg reloadverify"
                             style="height: 40px; right: 2px; margin-top: -43px; position: absolute; cursor: pointer;"
                             onclick="changeCode(this)"
                             src="/index.php?act=seccode&op=makecode&nchash=<?php echo getNchash(); ?>">
                    </div>
                </div>
            </div>
            <div class="form-group">
                <div class="col-md-12">
                    <input name="btnLogin" class="btn btn-dropbox pull-right" id="btnLogin"
                           style="width: 100%; color: white; font-weight: bolder; cursor: pointer; background-color: rgb(206, 169, 35);"
                           type="submit" value="登 录">
                    <input type="hidden" value="<?php echo $_GET['ref_url'] ?>" name="ref_url">
                </div>
            </div>
            <!--<div class="form-group">
                <div class="col-md-12">
                    <a href="javascript:;" id="forget-password" class="forget-password">忘记用户名密码?</a>
                </div>
            </div>-->
            <div class="form-group"></div>
        </form>
        <form class="form-horizontal templatemo-contact-form-1 forget-form" action="index.html" method="post"
              style="display: none">
            <div class="form-group">
                <div class="col-md-12">
                    <h3 class="font-green">忘记密码?</h3>
                    <p id=" t_msg">请输入您的注册邮箱</p>
                </div>
            </div>
            <div class="form-group">
                <div class="col-md-12">
                    <input class="form-control placeholder-no-fix uname" id="user" type="text" autocomplete="off"
                           placeholder="请输入您的用户名" name="uname"/>
                </div>
            </div>			
            <div class="form-group">
                <div class="col-md-12">
                    <input class="form-control placeholder-no-fix email" id="iemail" type="text" autocomplete="off"
                           placeholder="请输入您的邮箱" name="email"/>
                </div>
            </div>
            <div class="form-group">
                <div class="col-md-12">
                    <div class="form-actions">
                        <button type="button" id="back-btn" class="btn green btn-outline">返回</button>
                        <button type="button" class="btn btn-success uppercase pull-right forget_pwd">
                            提交
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
<link href="/templates/default/home/login/templatemo_style.css" rel="stylesheet" type="text/css">
<script src="/templates/default/home/login/jquery-1.8.3.min.js" type="text/javascript"></script>
<script src="/templates/default/home/login/layer.js" type="text/javascript"></script>
<script src="/Public/Home/assets/global/plugins/jquery-validation/js/jquery.validate.min.js"
        type="text/javascript"></script>
<script src="/Public/Home/assets/pages/scripts/login.min.js" type="text/javascript"></script>
</body>
<script type="text/javascript">
    $(document).ready(function ($) {
        var display = $('.mobile').css('display');
        if (display == 'block') {
            $('.logo').attr('style', 'max-width: 500px');
            $('.content').attr('style', 'padding-bottom: 0px');
        }
        var verifyimg = $(".verifyimg").attr("src");
        $(".reloadverify").click(function () {
            if (verifyimg.indexOf('?') > 0) {
                $(".verifyimg").attr("src", '/index.php?act=seccode&op=makecode&nchash=<?php echo getNchash();?>&t=' + Math.random());
            } else {
                $(".verifyimg").attr("src", '/index.php?act=seccode&op=makecode&nchash=<?php echo getNchash();?>&t=' + Math.random());
            }
        });
    });
    $('.forget_pwd').click(function (event) {
        var uname = $('.uname').val();
        var email = $('.email').val();
        var typ = $('#typ').val();
        if (uname == '') {
            alert('请输入用户名');
            return false;
        }
        if (email == '') {
            alert('请输入邮箱');
            return false;
        }
        $.post("index.php?act=login&op=forget", {uname: uname, email: email, typ: typ}, function (data) {
            if (data.status) {
                alert(data.info);
            } else {
                alert(data.info);
            }
        }, 'json');
    });

    $('#typ').change(function () {
        var uname = $('.uname').val();

        if ($(this).val() == 'email') {
            $("#t_msg").html("请输入您的邮箱");
            $(".email").attr('placeholder', '请输入您的邮箱');
            return false;
        }
        if (uname == '') {
            alert("请输入用户名");
            $(this).val("email");
            return false;
        }

        $(".email").attr('placeholder', '请输入密保答案');
        $.post("index.php?act=login&op=getmb", {uname: uname}, function (data) {
            if (data.status) {
                $("#t_msg").html(data.info);
            } else {
                alert(data.info);
                $("#t_msg").html("请输入您的邮箱");
                $("#typ").val("email");
                $(".email").attr('placeholder', '请输入您的邮箱');
                return false;
            }
        }, 'json');
    });
</script>