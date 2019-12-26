<?php defined('InShopBN') or exit('Access Invalid!'); ?>
<div class="page-bar">
    <ul class="page-breadcrumb">
        <li>
            <a href="/">首页</a>
            <i class="fa fa-circle"></i>
        </li>
        <li>
            <span>用户注册</span>
        </li>
    </ul>
</div>
<div class="row">
    <div class="col-md-8 ">
        <div class="portlet light bordered">
            <div class="portlet-title">
                <div class="caption font-green">
                    <i class="icon-user font-green"></i>
                    <span class="caption-subject bold uppercase">账号基本信息</span>
                </div>
            </div>
            <div class="portlet-body form">
                <form role="form" class="form_info">
                    <div class="form-body">
                        <!-- <div class="form-group form-md-floating-label">
                            <input type="text" class="form-control" id="hybh" name="hybh" value="">
                            <label for="hybh">会员编号</label>
                            <span class="help-block"></span>
                        </div> -->
                        <div class="form-group form-md-floating-label">
                            <label for="yhm"><span style="color: red">*</span>用户名</label>
                            <input type="text" class="form-control" id="yhm" name="yhm"
                                   value="<?php echo $output['username'] ?>"
                                   placeholder="用于登录系统,不能使用中文"
                                <?php if (C('reg_auto_username')) { ?> readonly<?php } ?>>
                            <span class="help-block"></span>
                        </div>
                        <div class="form-group form-md-floating-label">
                            <label for="email"><span style="color: red">*</span>邮箱</label>
                            <input type="email" class="form-control" id="email" name="email" value="cs1@qq.com"
                                   placeholder="请填写您的邮箱">

                        </div>
                        <div class="form-group form-md-floating-label">
                            <label for="dlmm"><span style="color: red">*</span>登录密码</label>
                            <input type="password" class="form-control" id="dlmm" name="dlmm" value="111111">

                        </div>
                        <div class="form-group form-md-floating-label">
                            <label for="qrdlmm"><span style="color: red">*</span>确认登录密码</label>
                            <input type="password" class="form-control" id="qrdlmm" name="qrdlmm" value="111111">
                        </div>
                        <div class="form-group form-md-floating-label">
                            <label for="jymm"><span style="color: red">*</span>二级密码</label>
                            <input type="password" class="form-control" id="jymm" name="jymm" value="222222"
                                   placeholder="用于敏感信息操作时验证">

                        </div>
                        <div class="form-group form-md-floating-label">
                            <label for="qrjymm"><span style="color: red">*</span>确认交易密码</label>
                            <input type="password" class="form-control" id="qrjymm" name="qrjymm" value="222222">

                        </div>
                        <div class="form-group form-md-floating-label">
                            <label for="group_id">会员级别</label>
                            <select class="form-control edited" id="group_id" name="group_id">
                                <?php foreach ($output['groupList'] as $item) { ?>
                                    <option value="<?php echo $item['group_id'] ?>">
                                        <?php echo $item['name'] ?>--(￥<?php echo $item['lsk'] ?>)
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="form-group form-md-floating-label">
                            <label for="name"><span style="color: red">*</span>真实姓名:</label>
                            <input type="text" class="form-control" id="name" name="name" value="测试">
                        </div>
                        <div class="form-group form-md-floating-label">
                            <label for="sex">性别:</label>
                            <label class="radio-inline">
                                <input type="radio" name="sex" value="1" checked> 男
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="sex" value="0"> 女
                            </label>
                        </div>
                        <!--<div class="form-group form-md-floating-label">
                            <label for="mbwt">密保问题</label>
                            <select class="form-control edited" id="mbwt" name="mbwt">
                                <option value="您高中班主任的名字是?">您高中班主任的名字是?</option>
                                <option value="您的小学校名是?">您的小学校名是?</option>
                                <option value="您母亲的生日是?">您母亲的生日是?</option>
                                <option value="您配偶的姓名是?">您配偶的姓名是?</option>
                                <option value="您的学号（或工号）是?">您的学号（或工号）是?</option>
                                <option value="您的出生地是?">您的出生地是?</option>
                                <option value="您小学班主任的名字是?">您小学班主任的名字是?</option>
                                <option value="您父亲的姓名是?">您父亲的姓名是?</option>
                                <option value="您配偶的生日是?">您配偶的生日是?</option>
                                <option value="您初中班主任的名字是?">您初中班主任的名字是?</option>
                                <option value="您母亲的姓名是?">您母亲的姓名是?</option>
                                <option value="您父亲的生日是?">您父亲的生日是?</option>
                            </select>
                        </div>-->
                        <!--<div class="form-group form-md-floating-label">
                            <label for="mbda"><span style="color: red">*</span>密保答案</label>
                            <input type="text" class="form-control" id="mbda" name="mbda" value="222222">

                        </div>-->
                        <div class="portlet-title">
                            <div class="caption font-green">
                                <i class=" icon-globe font-green"></i>
                                <span class="caption-subject bold uppercase">网络关系</span>
                            </div>
                        </div>
                        <br>
                        <div class="form-group form-md-floating-label">
                            <label for="tjr"><span style="color: red">*</span>推荐人</label>
                            <input type="text" class="form-control" id="tjr" name="tjr"
                                   value="<?php echo $output['tjrInfo']['username'] ?>">
                        </div>
                        <div class="form-group form-md-floating-label">
                            <label for="tjr"><span style="color: red">*</span>报单中心名称</label>
                            <input type="text" class="form-control" id="ssname" name="ssname"
                                   value="admin">
                        </div>
                        <div class="form-group form-md-floating-label">
                            <label for="jdr"><span style="color: red">*</span>接点人</label>
                            <input type="text" class="form-control" id="jdr" name="jdr"
                                   value="<?php echo $output['jdrInfo']['username']; ?>" readonly>
                        </div>
                        <div class="form-group form-md-floating-label">
                            <label for="scwz"><span style="color: red">*</span>市场位置</label>
                            <select class="form-control edited" id="scwz" name="scwz">
                                <option value="1"
                                    <?php if ($output['jdrInfo']['wz'] == 1) { ?>
                                        selected=""<?php } ?>>
                                    左区
                                </option>
                                <option value="2"
                                    <?php if ($output['jdrInfo']['wz'] == 2) { ?>
                                        selected=""<?php } ?>>
                                    右区
                                </option>
                            </select>
                        </div>
                        <div class="portlet-title">
                            <div class="caption font-green">
                                <i class="icon-credit-card"></i>
                                <span class="caption-subject bold uppercase">个人基本信息(选填)</span>
                            </div>
                        </div>
                        <br>
                        <?php if ($output['setting']['idcard']) { ?>
                            <div class="form-group form-md-floating-label">
                                <label for="sfzh">身份证号</label>
                                <input type="text" class="form-control" id="sfzh" name="sfzh" value="">

                            </div>
                            <?php
                        }
                        if ($output['setting']['addr']) {
                            ?>
                            <div class="form-group form-md-floating-label">
                                <label for="dz">地址</label>
                                <input type="text" class="form-control" id="dz" name="dz" value="">

                            </div>
                            <?php
                        }
                        if ($output['setting']['tel']) {
                            ?>
                            <div class="form-group form-md-floating-label">
                                <label for="tel">手机</label>
                                <input type="text" class="form-control" id="tel" name="tel" value="">

                            </div>
                            <?php
                        }
                        if ($output['setting']['qq']) {
                            ?>
                            <div class="form-group form-md-floating-label">
                                <label for="qq">QQ</label>
                                <input type="text" class="form-control" id="qq" name="qq" value="">

                            </div>
                            <?php
                        }
                        ?>
                    </div>
                    <div class="form-actions noborder">
                        <button type="button" class="btn blue tj">提交信息</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<script>
    var doing = false;

    $('.tj').click(function (event) {
        /*       var hybh= $('#hybh').val();*/
        var yhm = $('#yhm').val();
        var dlmm = $('#dlmm').val();
        var qrdlmm = $('#qrdlmm').val();
        var jymm = $('#jymm').val();
        var qrjymm = $('#qrjymm').val();
        var hyjb = $('#hyjb').val();
        var mbwt = $('#mbwt').val();
        //var mbda = $('#mbda').val();
        var tjr = $('#tjr').val();
        var jdr = $('#jdr').val();
        var scwz = $('#scwz').val();
        /*var yhkh = $('#yhkh').val();*/
        var sfzh = $('#sfzh').val();
        /*var khh = $('#khh').val();*/
        var dz = $('#dz').val();
        var tel = $('#tel').val();
        var qq = $('#qq').val();
        var email = $('#email').val();
        var name = $('#name').val();
        var ssname = $('#ssname').val();
        /* if (tel == '' && qq == '') {
         sweetAlert("错误!", "手机号和QQ号必须填写一项", "error");
         return false;
         }*/
        /*if (dz == '') {
         sweetAlert("错误!", "请填写地址", "error");
         return false;
         } */
        /*if (sfzh == '') {
         sweetAlert("错误!", "请填写身份证号", "error");
         return false;
         }*/
        /* if (yhkh == '') {
         sweetAlert("错误!", "请填写银行卡号", "error");
         return false;
         }*/

        if (email == '') {
            sweetAlert("错误!", "请填写邮箱", "error");
            return false;
        }
        var myreg = /^([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+@([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+\.[a-zA-Z]{2,3}$/;
        if (!myreg.test(email)) {
            alert('提示\n\n请输入有效的E_mail！');
            return false;
        }
        if (jdr == '') {
            sweetAlert("错误!", "请填写接点人编号", "error");
            return false;
        }
        if (tjr == '') {
            sweetAlert("错误!", "请填写推荐人编号", "error");
            return false;
        }
        /*
         if (mbda == '') {
         sweetAlert("错误!", "请填写密保答案", "error");
         return false;
         }
         */
        if (name == '') {
            sweetAlert("错误!", "请填写真实姓名", "error");
            return false;
        } else {
            var reg_name = /[\u4E00-\u9FA5]{2}/;
            if (!(reg_name.test(name))) {
                sweetAlert("错误!", "真实姓名至少为2个中文", "error");
                return false;
            }
        }
        if (ssname == '') {
            sweetAlert("错误!", "请填写服务站", "error");
            return false;
        }
        if (yhm == '') {
            sweetAlert("错误!", "请填写用户名", "error");
            return false;
        } else {
            if (/.*[\u4e00-\u9fa5]+.*$/.test(yhm)) {
                sweetAlert("错误!", "用户名不能使用中文", "error");
                return false;
            }
        }
        if (dlmm == '') {
            sweetAlert("错误!", "请填写密码", "error");
            return false;
        } else {
            if (dlmm != qrdlmm) {
                sweetAlert("错误!", "两次输入的密码不一样", "error");
                return false;
            }
        }
        if (jymm == '') {
            sweetAlert("错误!", "请填写交易密码", "error");
            return false;
        } else {
            if (jymm != qrjymm) {
                sweetAlert("错误!", "两次输入的密码不一样", "error");
                return false;
            }
        }

        if (doing) {
            sweetAlert("已经提交，请耐心等待", "error");
            return false;
        }

        doing = true;

        $.post("index.php?act=team&op=do_register", $('.form_info').serialize(), function (data) {
            if (data.status) {
                swal("成功!", "激活该用户才能收益", "success");
                window.setTimeout('location.href="index.php?act=team&op=activation"', '1000');
            } else {
                swal("失败!", data.info, "error");
            }
            doing = false;
        }, 'json');

    });
</script>