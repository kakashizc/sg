<?php defined('InShopBN') or exit('Access Invalid!'); ?>
<link href="/Public/Home/assets/global/plugins/bootstrap-daterangepicker/daterangepicker.min.css" rel="stylesheet"
      type="text/css"/>
<link href="/Public/Home/assets/global/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css" rel="stylesheet"
      type="text/css"/>
<link href="/Public/Home/assets/global/plugins/bootstrap-timepicker/css/bootstrap-timepicker.min.css" rel="stylesheet"
      type="text/css"/>
<link href="/Public/Home/assets/global/plugins/bootstrap-datetimepicker/css/bootstrap-datetimepicker.min.css"
      rel="stylesheet" type="text/css"/>

<div class="page-bar">
    <ul class="page-breadcrumb">
        <li>
            <a href="/">首页</a>
            <i class="fa fa-circle"></i>
        </li>
        <li>
            <span>转账汇款</span>
        </li>
    </ul>
</div>
<div class="row ">
    <div class="portlet box purple">
        <div class="portlet-title">
            <div class="caption">
                <i class="fa fa-gift"></i>充值方式
            </div>
            <div class="tools">
                <a href="javascript:;" class="collapse" data-original-title="" title=""> </a>
            </div>
        </div>
        <div class="portlet-body" style="display: none;">
            <div class="row ">
                <div class="col-md-4">
                    <dl>
                        <dt>中国工商银行</dt>
                        <dd><?php echo $output['info']['icbc']; ?></dd>
                        <dt>中国农业银行</dt>
                        <dd><?php echo $output['info']['abc']; ?></dd>
                        <dt>中国建设银行</dt>
                        <dd><?php echo $output['info']['ccb']; ?></dd>
                        <dt>中国银行</dt>
                        <dd><?php echo $output['info']['boc']; ?></dd>
                        <br/>
                        <dt style="color:red;">人民币兑换比例1:1</dt>
                    </dl>
                </div>
                <div class="col-md-4">
                    <dt>支付宝</dt>
                    <dd><?php echo $output['info']['alipay']; ?></dd>
                    <img
                        src="<?php echo UPLOAD_SITE_URL . '/' . (ATTACH_PATH . '/payimg/' . $output['info']['aliimg']); ?>"
                        height="150px" width="150px">
                </div>
                <div class="col-md-4">
                    <dt>微信</dt>
                    <dd><?php echo $output['info']['wx']; ?></dd>
                    <img
                        src="<?php echo UPLOAD_SITE_URL . '/' . (ATTACH_PATH . '/payimg/' . $output['info']['wximg']); ?>"
                        height="150px" width="150px">
                </div>
            </div>

        </div>
    </div>
    <div class="portlet light bordered col-md-8">
        <div class="portlet-title">
            <div class="caption">
                <i class="icon-settings font-dark"></i>
                <span class="caption-subject font-dark sbold uppercase">汇款信息填写</span>
                <span class="caption-subject font-dark sbold uppercase"><a href="index.php?act=fin&op=hklog">查看汇款记录</a></span>
            </div>
        </div>
        <div class="portlet-body form">
            <form class="form-horizontal" role="form" method="post" action="index.php?act=fin&op=DoHk">
                <div class="form-body">
                    <div class="form-group">
                        <label class="col-md-3 control-label">汇款方式</label>
                        <div class="col-md-9">
                            <select class="form-control type" name="type" onChange="change_type()">
                                <option value="1">银行转账</option>
                                <option value="2">支付宝转账</option>
                                <option value="3">微信转账</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group bank" style="display: ;">
                        <label class="col-md-3 control-label">银行名称</label>
                        <div class="col-md-9">
                            <select class="form-control changebank" name="changebank" onChange="change_bank()">
                                <option value="1">中国银行</option>
                                <option value="2">中国农业银行</option>
                                <option value="3">中国建设银行</option>
                                <option value="4">中国工商银行</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group bank_zh">
                        <label class="col-md-3 control-label">收款账号</label>
                        <div class="col-md-9">
                            <input type="text" class="form-control bankzh" name="bankzh"
                                   value="<?php echo $output['info']['boc']; ?>" readonly>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">汇款账号</label>
                        <div class="col-md-9">
                            <input type="text" class="form-control hkzh" name="hkzh" required
                                   placeholder="银行汇款请填写银行卡号,支付宝微信请填写对应账号">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">账户名:</label>
                        <div class="col-md-9">
                            <input type="text" class="form-control zhm" required name="zhm" placeholder="汇款开户名">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">汇款金额</label>
                        <div class="col-md-9">
                            <input type="text" class="form-control jine" required name="jine" placeholder="汇款金额">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">时间</label>
                        <div class="col-md-9">
                            <div class="input-group input-medium date date-picker" data-date-format="yyyy-mm-dd"
                                 data-date-start-date="+0d">
                                <input type="text" class="form-control time" required name="time">
                                                        <span class="input-group-btn">
                                                            <button class="btn default" type="button">
                                                                <i class="fa fa-calendar"></i>
                                                            </button>
                                                        </span>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-md-3 control-label">手机</label>
                        <div class="col-md-9">
                            <input type="text" class="form-control tel" required name="tel" placeholder="请填写您的手机号">
                        </div>
                    </div>


                    <div class="form-group">
                        <label class="col-md-3 control-label">充值描述</label>
                        <div class="col-md-9">
                            <textarea class="form-control intro" required name="intro" rows="3"
                                      placeholder="例如:报单币:500"></textarea>
                        </div>
                    </div>
                </div>
                <div class="form-actions">
                    <div class="row">
                        <div class="col-md-offset-3 col-md-9">
                            <button type="submit" class="btn green tj">提交</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="/Public/Home/assets/global/plugins/moment.min.js" type="text/javascript"></script>
<script src="/Public/Home/assets/global/plugins/bootstrap-daterangepicker/daterangepicker.min.js"
        type="text/javascript"></script>
<script src="/Public/Home/assets/global/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js"
        type="text/javascript"></script>
<script src="/Public/Home/assets/global/plugins/bootstrap-timepicker/js/bootstrap-timepicker.min.js"
        type="text/javascript"></script>
<script src="/Public/Home/assets/global/plugins/bootstrap-datetimepicker/js/bootstrap-datetimepicker.min.js"
        type="text/javascript"></script>
<script src="/Public/Home/assets/global/plugins/clockface/js/clockface.js" type="text/javascript"></script>
<!-- END PAGE LEVEL PLUGINS -->
<!-- BEGIN THEME GLOBAL SCRIPTS -->
<script src="/Public/Home/assets/global/scripts/app.min.js" type="text/javascript"></script>
<!-- END THEME GLOBAL SCRIPTS -->
<!-- BEGIN PAGE LEVEL SCRIPTS -->
<script src="/Public/Home/assets/pages/scripts/components-date-time-pickers.min.js" type="text/javascript"></script>


<script>
    function change_type() {
        var v = $('.type').val();
        if (v == 1) {
            var b = $('.changebank').val();
            if (b == 1) {
                $('.bankzh').val('<?php echo $output['info']['boc'];?>');
            } else if (b == 2) {
                $('.bankzh').val('<?php echo $output['info']['abc'];?>');
            } else if (b == 3) {
                $('.bankzh').val('<?php echo $output['info']['ccb'];?>');
            } else if (b == 4) {
                $('.bankzh').val('<?php echo $output['info']['icbc'];?>');
            }
            $('.bank').css('display', '');
        } else if (v == 2) {
            $('.bankzh').val('<?php echo $output['info']['alipay'];?>');
            $('.bank').css('display', 'none');
        } else if (v == 3) {
            $('.bankzh').val('<?php echo $output['info']['wx'];?>');
            $('.bank').css('display', 'none');
        }
    }
    function change_bank() {
        var b = $('.changebank').val();
        if (b == 1) {
            $('.bankzh').val('<?php echo $output['info']['boc'];?>');
        } else if (b == 2) {
            $('.bankzh').val('<?php echo $output['info']['abc'];?>');
        } else if (b == 3) {
            $('.bankzh').val('<?php echo $output['info']['ccb'];?>');
        } else if (b == 4) {
            $('.bankzh').val('<?php echo $output['info']['icbc'];?>');
        }
    }
</script>