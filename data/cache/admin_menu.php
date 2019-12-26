<?php defined('InShopBN') or exit('Access Invalid!'); return array (
  'system' => 
  array (
    'name' => '平台',
    'child' => 
    array (
      0 => 
      array (
        'name' => '设置',
        'child' => 
        array (
          'setting' => '站点设置',
          'message' => '邮件设置',
          'cs' => '参数设置',
          'reg_cs' => '注册设置',
          'admin' => '权限设置',
          'admin_log' => '操作日志',
          'cache' => '清理缓存',
          'wipe_data' => '清空数据',
        ),
      ),
      1 => 
      array (
        'name' => '数据',
        'child' => 
        array (
          'db' => '数据库管理',
        ),
      ),
      2 => 
      array (
        'name' => '文章',
        'child' => 
        array (
          'article' => '文章列表',
          'article_class' => '文章分类',
        ),
      ),
      3 => 
      array (
        'name' => '商品',
        'child' => 
        array (
          'goods' => '商品列表',
          'goods&op=goods_class' => '商品分类',
          'orders' => '订单列表',
        ),
      ),
    ),
  ),
  'member' => 
  array (
    'name' => '会员',
    'child' => 
    array (
      0 => 
      array (
        'name' => '会员',
        'child' => 
        array (
          'member' => '会员列表',
          'biz' => '服务站列表',
          'inbox' => '收件箱',
          'outbox' => '发件箱',
          'net' => '网络图',
          'wage' => '分润工资',
        ),
      ),
    ),
  ),
  'fin' => 
  array (
    'name' => '财务',
    'child' => 
    array (
      0 => 
      array (
        'name' => '财务',
        'child' => 
        array (
          'fin' => '财务记录',
          'bao' => '奖金记录',
          'dian' => '奖金详情',
          'xu' => '奖金拨比',
          'remit' => '汇款管理',
          'remit_tx' => '提现管理',
          'txsuccess' => '提现成功',
          'txfail' => '提现失败',
        ),
      ),
    ),
  ),
  'stat' => 
  array (
    'name' => '统计',
    'child' => 
    array (
      0 => 
      array (
        'name' => '会员',
        'child' => 
        array (
          'stat_member' => '新增用户',
          'stat_member_active' => '激活用户',
        ),
      ),
    ),
  ),
);