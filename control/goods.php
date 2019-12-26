<?php
/**
 * 商品
 ***/


defined('InShopBN') or exit('Access Invalid!');

class goodsControl extends BaseMemberControl
{
    public function __construct()
    {
        parent::__construct();
        Tpl::output('Shop_index_active', "active");
    }

    /**
     * 默认进入页面
     */
    public function indexOp()
    {
        exit;
    }

    /**
     * 商品列表显示页面
     */
    public function shopOp()
    {
        $goods_model = Model('goods');
        $goods_class_model = Model('goods_class');
        $classInfo = $goods_class_model->getClassList(array('order' => 'id asc'));
        /**
         * 读取语言包
         */
        Language::read('home_article_index');
        $lang = Language::getLangContent();
        $condition = array();
        if ($_GET['typeid']) {
            $condition['typeid'] = intval($_GET['typeid']);
            $curClassName = $goods_class_model->getOneClassField($condition['typeid'], 'typename');
        }
        $condition['status'] = '1';
        $page = new Page();
        $page->setEachNum(15);
        $page->setStyle('admin');
        $goodsList = $goods_model->getGoodsList($condition, $page);
        if ($goodsList) {
            foreach ($goodsList as &$g) {
                $g['thumb'] = UPLOAD_SITE_URL . '/' . $g['thumb'];
            }
        }
        Tpl::output('goodsList', $goodsList);
        Tpl::output('classInfo', $classInfo);
        Tpl::output('curClassName', $curClassName);
        Tpl::output('show_page', $page->show());
        Tpl::output('User_shop_dian', "active");
        Tpl::showpage('goods_list');
    }

    /**
     * 商品详情显示页面
     */
    public function showOp()
    {
        $this->check_jy_pwd();
        /**
         * 读取语言包
         */
        Language::read('home_article_index');
        $lang = Language::getLangContent();
        if (empty($_GET['id'])) {
            showMessage($lang['para_error'], '', 'html', 'error');//'缺少参数:文章编号'
        }
        //获取用户信息
        $member_model = Model('member');
        $userInfo = $member_model->getMemberInfoByID($this->userid);
        /**
         * 根据文章编号获取文章信息
         */
        $goods_model = Model('goods');
        $goods_class_model = Model('goods_class');
        $goods = $goods_model->getOneGoods(intval($_GET['id']));
        $goods['thumb'] = UPLOAD_SITE_URL . '/' . $goods['thumb'];
        $goods['typename'] = $goods_class_model->getOneClassField($goods['typeid'], 'typename');
        if (empty($goods) || !is_array($goods) || $goods['status'] == '0') {
            showMessage($lang['article_show_not_exists'], '', 'html', 'error');//'该文章并不存在'
        }
        Tpl::output('goods', $goods);
        Tpl::output('userInfo', $userInfo);

        /**
         * 根据类别编号获取文章类别信息
         */
        $goods_class_model = Model('goods_class');
        $condition = array();
        $goods_class = $goods_class_model->getOneClass($goods['typeid']);
        if (empty($goods_class) || !is_array($goods_class)) {
            showMessage($lang['article_show_delete'], '', 'html', 'error');//'该文章已随所属类别被删除'
        }
        Tpl::output('Message_Gonggao_selected', "active");

        Tpl::showpage('goods_show');
    }

    //商品购买
    public function order_buyOp()
    {

        $member_model = Model('member');
        $goods_model = Model('goods');
        $goods_order_model = Model('goods_order');
        $yulebao_model = Model('yulebao');
        $trans = Model('trans');

        $gid = $_POST['gid'];
        $uid = $this->userid;
        $num = intval($_POST['num']) ? intval($_POST['num']) : 0;
        if (!$num) {
            $this->error('请检查商品购买数量！');
        }
        //获取商品信息
        $goodInfo = $goods_model->getOneGoods($gid);
        if (empty($goodInfo)) {
            $this->error('无效商品！');
        }
        if ($num > $goodInfo['stock']) {
            $this->error('库存不足！');
        }
        $goods_name = $goodInfo['goods_name'];
        $goods_typeid = $goodInfo['typeid'];
        $unit_price = $goodInfo['price'];
        $ship_fee = $goodInfo['ship_fee'] * $num;
        $total = $unit_price * $num + $ship_fee;
        $consignee = trim($_POST['consignee']);
        $address = trim($_POST['address']);
        $tel = trim($_POST['tel']);

        $time = time();
        $fanli = $goodInfo['fanli'];
        $beout = $goodInfo['beout'];

        //获取用户信息
        $userInfo = $member_model->getMemberInfoByID($uid);
        if ($userInfo['status'] == 0) {
            $this->error('未激活账户不能购买商品!');
        }
        if ($goods_typeid == 1) {
            if ($userInfo['jinbi_balance'] < $total) {
                $this->error('购买娱乐包，金币余额不足！');
            } else {
                $setting_model = Model('setting');
                $con_yulebao_limit = $setting_model->getRowSetting('con_yulebao_limit');
                //会员已购买的娱乐包
                $getCount['uid'] = $uid;
                $yulebao_count = $yulebao_model->getCount($getCount);
                if ($yulebao_count + $num > $con_yulebao_limit['value']) {
                    $this->error('已超出娱乐包购买上限');
                }
                //娱乐包进行支付
                $member_where = array(
                    'id' => $uid
                );
                $member_updata = array(
                    'jinbi_balance' => $userInfo['jinbi_balance'] - $total,
                );
                $chenck_memeber = $member_model->editMember($member_where, $member_updata);
            }
        } else {
            if ($userInfo['ji_balance'] + $userInfo['zhang_balance'] < $total) {
                $this->error('账户总余额不足！');
            }
            //普通商品支付,优先扣除购物币
            $surplusMoney = $userInfo['zhang_balance'] - $total;
            if ($surplusMoney >= 0) {
                $gwb_balance = $surplusMoney;
                $jinbi_balance = $userInfo['ji_balance'];
                $xiaofei_gwb = $total;
                $xiaofei_jinbi = 0;
            } elseif ($surplusMoney < 0) {
                $gwb_balance = 0;
                $jinbi_balance = $userInfo['ji_balance'] - abs($surplusMoney);
                $xiaofei_gwb = $userInfo['zhang_balance'];
                $xiaofei_jinbi = abs($surplusMoney);
            }
            $member_where = array(
                'id' => $uid
            );
            $member_updata = array(
                'ji_balance' => $jinbi_balance,
                'zhang_balance' => $gwb_balance,
                'name' => !empty($consignee) ? $consignee : $userInfo['name'],
                'address' => !empty($address) ? $address : $userInfo['address'],
                'tel' => !empty($tel) ? $tel : $userInfo['tel'],
            );
            $chenck_memeber = $member_model->editMember($member_where, $member_updata);
        }
        if ($chenck_memeber) {
            //订单成功支付入库
            $dataOrder['uid'] = $uid;
            $dataOrder['username'] = $userInfo['username'];
            $dataOrder['gid'] = $gid;
            $dataOrder['goods_name'] = $goods_name;
            $dataOrder['goods_typeid'] = $goods_typeid;
            $dataOrder['unit_price'] = $unit_price;
            $dataOrder['num'] = $num;
            $dataOrder['ship_fee'] = $ship_fee;
            $dataOrder['total'] = $total;
            $dataOrder['addtime'] = $time;
            $dataOrder['status'] = $goods_typeid == 1 ? 1 : 1;
            $dataOrder['consignee'] = $consignee;
            $dataOrder['address'] = $address;
            $dataOrder['tel'] = $tel;
            $checkOrder = $goods_order_model->add($dataOrder);
            if ($checkOrder) {
                //更新库存
                $update_goods = array(
                    'gid' => $gid,
                    'stock' => $goodInfo['stock'] - $num
                );
                $goods_model->updates($update_goods);

                if ($goods_typeid == 1) {
                    $money = '-' . $total;
                    $trans->recorde($this->userid, $money, '金币', '购买娱乐包', '购买：' . $goods_name . ',购买数量：' . $num);
                } else {
                    $money = '-' . $total;
                    $beizhu = '购买：' . $goods_name . ',购买数量：' . $num . ',消费购物币:' . $xiaofei_gwb . ',积分补差价:' . $xiaofei_jinbi;
                    $trans->recorde($this->userid, $money, '购物币', '购买商品', $beizhu);
                }
                if ($goods_typeid == 1) {
                    //娱乐包直推奖
                    $bonus = Model('bonus');
                    $bonus->zhitui($total, $goods_name, $num, $unit_price, $userInfo['pid'], $userInfo['id'], $userInfo['username']);
                    //娱乐包入库
                    $dataYulebao ['uid'] = $uid;
                    $dataYulebao ['username'] = $userInfo['username'];
                    $dataYulebao ['gid'] = $gid;
                    $dataYulebao ['typeid'] = $goods_typeid;
                    $dataYulebao ['price'] = $unit_price;
                    $dataYulebao ['fanli'] = $fanli;
                    $dataYulebao ['beout'] = $beout;
                    $dataYulebao ['addtime'] = $time;
                    $dataYulebao ['goods_name'] = $goods_name;
                    for ($i = 1; $i <= $num; $i++) {
                        $yulebao_model->add($dataYulebao);
                    }
                }
                $result['status'] = 1;
            }

        } else {
            $this->error('支付失败!');
        }
        echo json_encode($result);
    }

    //加入购物车
    public function add_cartOp()
    {
        $member_model = Model('member');
        $goods_model = Model('goods');
        $goods_order_model = Model('goods_order');
        $trans = Model('trans');

        $gid = $_POST['gid'];
        $uid = $this->userid;
        $num = intval($_POST['num']) ? intval($_POST['num']) : 1;
        if (!$num) {
            $this->error('请检查商品购买数量！');
        }
        //获取商品信息
        $goodInfo = $goods_model->getOneGoods($gid);
        if (empty($goodInfo)) {
            $this->error('无效商品！');
        }
        if ($num > $goodInfo['stock']) {
            $this->error('库存不足！');
        }
        $goods_name = $goodInfo['goods_name'];
        $goods_typeid = $goodInfo['typeid'];
        $unit_price = $goodInfo['price'];
        $ship_fee = $goodInfo['ship_fee'] * $num;
        $total = $unit_price * $num + $ship_fee;
        $consignee = trim($_POST['consignee']);
        $address = trim($_POST['address']);
        $tel = trim($_POST['tel']);

        $time = time();

        //获取用户信息
        $userInfo = $member_model->getMemberInfoByID($uid);
        if ($userInfo['status'] == 0) {
            $this->error('未激活账户不能购买商品!');
        }
        //订单加入购物车
        $dataOrder['uid'] = $uid;
        $dataOrder['username'] = $userInfo['username'];
        $dataOrder['gid'] = $gid;
        $dataOrder['goods_name'] = $goods_name;
        $dataOrder['goods_typeid'] = $goods_typeid;
        $dataOrder['unit_price'] = $unit_price;
        $dataOrder['num'] = $num;
        $dataOrder['ship_fee'] = $ship_fee;
        $dataOrder['total'] = $total;
        $dataOrder['addtime'] = $time;
        $dataOrder['status'] = 0;
        $dataOrder['consignee'] = $consignee ? $consignee : $userInfo['consignee'];
        $dataOrder['address'] = $address ? $address : $userInfo['address'];
        $dataOrder['tel'] = $tel ? $tel : $userInfo['tel'];
        $checkOrder = $goods_order_model->add($dataOrder);
        if ($checkOrder) {
            $result['status'] = 1;
        } else {
            $result['status'] = 0;
            $result['info'] = '加入购物车失败！';
        }
        echo json_encode($result);
    }


    //查看购物车
    public function userCartOp()
    {
        $orders_model = Model('goods_order');
        $page = new Page();
        $page->setEachNum(100);
        $page->setStyle('admin');
        $where = array(
            'uid' => $this->userid,
            'status' => 'cart'
        );
        $orderList = $orders_model->getGoodsOrderList($where, $page);
        Tpl::output('show_page', $page->show());
        Tpl::output('orderList', $orderList);
        Tpl::output('User_cart_dian', "active");
        Tpl::showpage('user.cart');
    }

    //购物车结算
    public function userCartBuyOp()
    {
        $member_model = Model('member');
        $goods_model = Model('goods');
        $goods_order_model = Model('goods_order');
        $trans = Model('trans');
        $uid = $this->userid;
        $all_money = 0;
        $where = array(
            'uid' => $this->userid,
            'status' => 'cart'
        );
        $orderList = $goods_order_model->getGoodsOrderList($where);
        foreach ($orderList as $item) {
            $goodsInfo = $goods_model->getOneGoods($item['gid']);
            if ($goodsInfo) {
                if ($goodsInfo['stock'] < $item['num']) {
                    showMessage($item['goods_name'] . '库存不足请删除！', '', 'html', 'error');
                }
            } else {
                showMessage($item['goods_name'] . '已下架！', '', 'html', 'error');
            }
            $all_money += $item['total'];
        }
        //获取用户信息
        $userInfo = $member_model->getMemberInfoByID($uid);
        if ($userInfo['ji_balance'] + $userInfo['zhang_balance'] < $all_money) {
            $this->error('账户总余额不足！');
        }
        //普通商品支付,优先扣除购物币
        $surplusMoney = $userInfo['zhang_balance'] - $all_money;
        if ($surplusMoney >= 0) {
            $gwb_balance = $surplusMoney;
            $jinbi_balance = $userInfo['ji_balance'];
            $xiaofei_gwb = $all_money;
            $xiaofei_jinbi = 0;
        } elseif ($surplusMoney < 0) {
            $gwb_balance = 0;
            $jinbi_balance = $userInfo['ji_balance'] - abs($surplusMoney);
            $xiaofei_gwb = $userInfo['zhang_balance'];
            $xiaofei_jinbi = abs($surplusMoney);
        }
        $member_where = array(
            'id' => $uid
        );
        $member_updata = array(
            'ji_balance' => $jinbi_balance,
            'zhang_balance' => $gwb_balance
        );
        $chenck_memeber = $member_model->editMember($member_where, $member_updata);
        if ($chenck_memeber) {
            $money = '-' . $all_money;
            $beizhu = '购物车结算,消费购物币:' . $xiaofei_gwb . ',积分补差价:' . $xiaofei_jinbi;
            $trans->recorde($this->userid, $money, '购物币', '购买商品', $beizhu);
            $check = $goods_order_model->clear_cart($uid);
            if ($check) {
                showMessage('购物结算成功！', 'index.php?act=goods&op=userOrders');
            } else {
                showMessage('购物结算失败！');
            }
        }
    }

    //查看订单
    public function userOrdersOp()
    {
        $orders_model = Model('goods_order');
        $page = new Page();
        $page->setEachNum(15);
        $page->setStyle('admin');
        $where = array(
            'uid' => $this->userid,
            'status' => 'order'
        );
        $orderList = $orders_model->getGoodsOrderList($where, $page);

        Tpl::output('show_page', $page->show());
        Tpl::output('orderList', $orderList);
        Tpl::output('User_orders_dian', "active");
        Tpl::showpage('user.orders');
    }

    //取消订单
    public function cancelOrderOp()
    {
        $orders_model = Model('goods_order');
        $member_model = Model('member');
        $uid = $this->userid;
        $order_id = intval($_GET['order_id']);
        $orderInfo = $orders_model->getOneGoodsOrder($order_id);
        if ($orderInfo) {
            if ($orderInfo['uid'] == $uid && $orderInfo['status'] != 2) {
                $check = $orders_model->del($order_id);
                if ($check) {
                    $member_where = array(
                        'id' => $uid
                    );
                    $member_updata = array(
                        'zhang_balance' => array('exp', 'zhang_balance +' . $orderInfo['total'])
                    );
                    $chenck_memeber = $member_model->editMember($member_where, $member_updata);
                    if ($chenck_memeber) {
                        showMessage('取消订单成功!');
                    } else {
                        showMessage('取消订单失败!');
                    }
                } else {
                    showMessage('取消订单失败!');
                }
            } else {
                showMessage('订单已发货,取消失败！');
            }
        } else {
            showMessage('不存在订单！');
        }

    }

    //查看我的娱乐包
    public function userYulebaoOp()
    {
        $yulebao_model = Model('yulebao');
        $page = new Page();
        $page->setEachNum(15);
        $page->setStyle('admin');
        $where = array(
            'uid' => $this->userid,
            'order' => 'id asc'
        );
        $yulebaoList = $yulebao_model->getYulebaoList($where, $page);
        Tpl::output('show_page', $page->show());
        Tpl::output('yulebaoList', $yulebaoList);
        Tpl::output('User_yulebao_dian', "active");
        Tpl::showpage('user.yulebao');
    }

    /**复投娱乐包
     * state 0 : 复投失败
     * state 1 : 复投成功
     * state 2 : 金币余额不足
     */
    public function yulebaofutouOp()
    {
        $id = $_GET['id'];
        $uid = $this->userid;
        $member_model = Model('member');
        $yulebao_model = Model('yulebao');
        $trans_model = Model('trans');
        $yulebaoInfo = $yulebao_model->getOneYulebao($id);
        //获取用户信息
        $userInfo = $member_model->getMemberInfoByID($uid);
        if ($userInfo['status'] == 0) {
            $this->error('未激活账户不能购买商品!');
        }
        if ($yulebaoInfo['status'] == 0) {
            if ($userInfo['jinbi_balance'] < $yulebaoInfo['price']) {
                $result['state'] = 2;
                $result['msg'] = '金币余额不足';
            } else {
                //更新娱乐包状态
                $update_array = array(
                    'id' => $yulebaoInfo['id'],
                    'curfanli' => 0,
                    'status' => 1
                );
                $check_yulebalo = $yulebao_model->updates($update_array);
                if ($check_yulebalo) {
                    $member_where = array(
                        'id' => $uid
                    );
                    $member_updata = array(
                        'jinbi_balance' => $userInfo['jinbi_balance'] - $yulebaoInfo['price'],
                    );
                    //扣除金币
                    $chenck_memeber = $member_model->editMember($member_where, $member_updata);
                    if ($chenck_memeber) {
                        $money = '-' . $yulebaoInfo['price'];
                        $trans_model->recorde($uid, $money, '金币', '复投娱乐包', '复投：' . $yulebaoInfo['goods_name']);
                        //娱乐包直推奖
                        $bonus = Model('bonus');
                        $bonus->zhitui($yulebaoInfo['price'], $yulebaoInfo['goods_name'], 1, $yulebaoInfo['price'], $userInfo['pid'], $userInfo['id'], $userInfo['username']);
                        $result['state'] = 1;
                        $result['msg'] = '复投成功';
                    }
                }
            }
        } else {
            $result['state'] = 0;
            $result['msg'] = '复投失败';
        }
        echo json_encode($result);
        exit;
    }

    /**
     * 删除娱乐包
     */
    public function yulebaodelOp()
    {
        $id = intval($_GET['id']);
        if ($id) {
            $yulebao_model = Model('yulebao');
            $check = $yulebao_model->del($id);
            if ($check) {
                $result = array(
                    'state' => 1,
                    'msg' => '删除成功！'
                );
            } else {
                $result = array(
                    'state' => 0,
                    'msg' => '删除失败！'
                );
            }
        } else {
            $result = array(
                'state' => 0,
                'msg' => '删除失败！'
            );
        }

        echo json_encode($result);
        exit;
    }

    public function sendOp()
    {
        Tpl::output('Message_index_selected', "active");
        Tpl::showpage('article.send');
    }

    public function DoSendOp()
    {
        $type = $_REQUEST['type'];
        $title = $_REQUEST['title'];
        $num = $_REQUEST['num'];
        $content = $_REQUEST['content'];

        $user = Model('member');
        $message = Model('message');

        if ($num != '') {
            $where = array('num' => $num);
            $UserInfo = $user->getMemberInfo($where);
            if (!$UserInfo) {
                exit(json_encode(array('info' => '该用户不存在!', 'status' => 0)));
            }
        }
        if ($type == 1) {
            $totype = 1;
            $toid = 'A';
        } else {
            $totype = 0;
            $toid = $UserInfo['id'];
        }

        $data = array('formid' => $this->userid, 'formname' => $this->username, 'totype' => $totype, 'toid' => $toid, 'title' => $title, 'content' => $content, 'addtime' => time());
        $res = $message->addMessage($data);
        if ($res) {
            exit(json_encode(array('info' => '发送成功', 'status' => 1)));
        } else {
            exit(json_encode(array('info' => '发送失败', 'status' => 0)));
        }
    }

    public function messageFjOp()
    {
        $message = Model("message");

        $condition = array();
        //$condition['typeid']	= $_GET['id'];
        //$condition['status']	= '1';
        $page = new Page();
        $page->setEachNum(15);
        $page->setStyle('admin');
        //$article_list	= $article_model->getArticleList($condition,$page);

        $where = array('fromid' => $this->userid);

        $list = $message->getMsgList($where, $page);
        Tpl::output('list', $list);

        Tpl::output('show_page', $page->show());

        Tpl::output('Message_MessageFj_selected', "active");
        Tpl::showpage('article.messageFj');
    }

    public function GetMessageFjOp()
    {
        $id = $_REQUEST['id'] + 0;
        $message = Model("message");

        $where = array('id' => $id);
        $info = $message->getMessage($where);
        exit(json_encode(array('info' => $info, 'status' => 1)));
    }

    public function messageSjOp()
    {
        $message = Model("message");

        $condition = array();
        //$condition['typeid']	= $_GET['id'];
        //$condition['status']	= '1';
        $page = new Page();
        $page->setEachNum(15);
        $page->setStyle('admin');
        //$article_list	= $article_model->getArticleList($condition,$page);

        $where = array('toid' => $this->userid);

        $list = $message->getMsgList($where, $page);
        Tpl::output('list', $list);

        Tpl::output('Message_MessageSj_selected', "active");
        Tpl::output('show_page', $page->show());
        Tpl::showpage('article.messageSj');
    }

    public function GetMessageSjOp()
    {
        $id = $_REQUEST['id'] + 0;
        $message = Model("message");

        $where = array('id' => $id, 'toid' => $this->userid);
        $info = $message->getMessage($where);
        $message->editMessage($where, array('status' => '0'));

        exit(json_encode(array('info' => $info, 'status' => 1)));
    }

}

?>
