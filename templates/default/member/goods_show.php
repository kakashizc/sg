<?php defined('InShopBN') or exit('Access Invalid!'); ?>
<style>
    .form-horizontal .control-label {
        text-align: center;
    }

    .detail img {
        max-width: 100%;
    }
</style>
<div class="page-bar">
    <ul class="page-breadcrumb">
        <li>
            <a href="/">首页</a>
            <i class="fa fa-circle"></i>
        </li>
        <li>
            <span>商品详情</span>
        </li>
    </ul>
</div>
<div class="row" style="margin-top: 10px">
    <div class="col-md-3">
        <div class="thumbnail">
            <img src="<?php echo $output['goods']['thumb']; ?>" alt="..." class="img-responsive" alt="Responsive image">
        </div>
    </div>
    <div class="col-md-9">
        <form class="form-horizontal form_info" role="form" onsubmit="return false">
            <div class="form-group">
                <label for="inputEmail3" class="col-sm-2 control-label">名称：</label>
                <div class="col-sm-10">
                    <input type="text" value="<?php echo $output['goods']['goods_name']; ?>" class="form-control"
                           disabled/>
                </div>
            </div>
            <div class="form-group">
                <label for="inputPassword3" class="col-sm-2 control-label">类型：</label>
                <div class="col-sm-10">
                    <input type="text" value="<?php echo $output['goods']['typename']; ?>" class="form-control"
                           disabled/>
                </div>
            </div>
            <div class="form-group">
                <label for="inputPassword3" class="col-sm-2 control-label">价钱：</label>
                <div class="col-sm-10">
                    <input type="text" value="<?php echo $output['goods']['price']; ?>" class="form-control" disabled/>
                </div>
            </div>
            <div class="form-group">
                <label for="inputPassword3" class="col-sm-2 control-label">购买数量：</label>
                <div class="col-sm-2">
                    <input type="text" value="1" name="num" id="num" class="form-control"/>
                </div>
                <label for="inputPassword3" class="col-sm-1 control-label">库存：</label>
                <div class="col-sm-2">
                    <input type="text" value="<?php echo $output['goods']['stock']; ?>" class="form-control" disabled/>
                </div>
                <label for="inputPassword3" class="col-sm-1 control-label">运费：</label>
                <div class="col-sm-2">
                    <input type="text" value="<?php echo $output['goods']['ship_fee']; ?>" class="form-control"
                           disabled/>
                </div>
            </div>
            <?php if ($output['goods']['typeid'] != 1) { ?>
                <div class="form-group">
                    <label for="inputPassword3" class="col-sm-2 control-label">收货人：</label>
                    <div class="col-sm-2">
                        <input type="text" value="<?php echo $output['userInfo']['name']; ?>" name="consignee"
                               id="consignee" class="form-control"/>
                    </div>
                    <label for="inputPassword3" class="col-sm-1 control-label">收货地址：</label>
                    <div class="col-sm-3">
                        <input type="text" value="<?php echo $output['userInfo']['address']; ?>" name="address"
                               id="address" class="form-control"/>
                    </div>
                    <label for="inputPassword3" class="col-sm-1 control-label">联系电话：</label>
                    <div class="col-sm-3">
                        <input type="text" value="<?php echo $output['userInfo']['tel']; ?>" name="tel" id="tel"
                               class="form-control"/>
                    </div>
                </div>
            <?php } ?>
            <div class="form-group">
                <div class="col-sm-offset-2 col-sm-1">
                    <input type="hidden" value="<?php echo $output['goods']['gid']; ?>" name="gid">
                    <button type="submit" class="btn btn-default buy_now">立即购买</button>
                </div>
                <div class="col-sm-1">
                    <input type="hidden" value="<?php echo $output['goods']['gid']; ?>" name="gid">
                    <button type="submit" class="btn btn-default add_cart">加入购物车</button>
                </div>
            </div>
        </form>
    </div>
</div>
<div class="row detail">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">商品详情</div>
            <div class="panel-body">
                <?php echo $output['goods']['content']; ?>
            </div>
        </div>
    </div>
</div>
<script>
    var doing = false;

    $('.buy_now').click(function (event) {

        var num = $('#num').val();
        var consignee = $('#consignee').val();
        var address = $('#address').val();
        var tel = $('#tel').val();

        if (num == '') {
            sweetAlert("错误!", "请填写购买数量", "error");
            return false;
        }
        var mynum = /^\+?[1-9]\d*$/;

        if (!mynum.test(num)) {
            sweetAlert("错误!", "购买数量必须为正整数", "error");
            return false;
        }
        <?php if($output['goods']['typeid'] != 1){?>
        if (consignee == '') {
            sweetAlert("错误!", "请填写收货人", "error");
            return false;
        }
        if (address == '') {
            sweetAlert("错误!", "请填写收货地址", "error");
            return false;
        }
        if (tel == '') {
            sweetAlert("错误!", "请填写联系电话", "error");
            return false;
        }
        var mytels = /^[\d-]*$/;

        if (!mytels.test(tel)) {
            sweetAlert("错误!", "请输入正确联系电话", "error");
            return false;
        }
        <?php }?>

        if (doing) {
            sweetAlert("已经提交，请耐心等待", "error");
            return false;
        }
        doing = true;

        $.post("index.php?act=goods&op=order_buy", $('.form_info').serialize(), function (data) {
            if (data.status) {
                swal("成功!", "本次购买成功！", "success");
                window.setTimeout('location.href="index.php?act=goods&op=userOrders"', '1000');
            } else {
                swal("失败!", data.info, "error");
            }
            doing = false;
        }, 'json');
    });

    $('.add_cart').click(function (event) {

        var num = $('#num').val();
        var consignee = $('#consignee').val();
        var address = $('#address').val();
        var tel = $('#tel').val();

        if (num == '') {
            sweetAlert("错误!", "请填写购买数量", "error");
            return false;
        }
        var mynum = /^\+?[1-9]\d*$/;

        if (!mynum.test(num)) {
            sweetAlert("错误!", "购买数量必须为正整数", "error");
            return false;
        }
        <?php if($output['goods']['typeid'] != 1){?>
        if (consignee == '') {
            sweetAlert("错误!", "请填写收货人", "error");
            return false;
        }
        if (address == '') {
            sweetAlert("错误!", "请填写收货地址", "error");
            return false;
        }
        if (tel == '') {
            sweetAlert("错误!", "请填写联系电话", "error");
            return false;
        }
        var mytels = /^[\d-]*$/;

        if (!mytels.test(tel)) {
            sweetAlert("错误!", "请输入正确联系电话", "error");
            return false;
        }
        <?php }?>

        if (doing) {
            sweetAlert("已经提交，请耐心等待", "error");
            return false;
        }
        doing = true;

        $.post("index.php?act=goods&op=add_cart", $('.form_info').serialize(), function (data) {
            if (data.status) {
                swal("成功!", "加入购物车成功！", "success");
                //window.setTimeout('location.href="index.php?act=goods&op=userOrders"', '1000');
            } else {
                swal("失败!", data.info, "error");
            }
            doing = false;
        }, 'json');
    });
</script>