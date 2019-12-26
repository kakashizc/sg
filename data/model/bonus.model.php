<?php
defined('InShopBN') or exit('Access Invalid!');

class bonusModel extends Model
{

    private $users_model;

    private $bonuslaiyuan_model;

    private $group_model;

    private $group;

    private $trans_model;

    private $bonuslog_model;

    private $net_model;

    private $biz_model;

    private $setting_model;

    private $record_model;

    private $parent_model;

    private $layerlist_model;


    public function __construct()

    {

        $this->users_model = Model('member');

        $this->bonuslaiyuan_model = Model('bonuslaiyuan');

        $this->group_model = Model('group');

        $this->group = $this->group_model->getAll();

        $this->trans_model = Model('trans');

        $this->bonuslog_model = Model('bonuslog');

        $this->net_model = Model('net');

        $this->biz_model = Model('biz');

        $this->setting_model = Model('setting');

        $this->record_model = Model('record');

        $this->parent_model = Model('parent');

        $this->layerlist_model = Model('layerlist');

    }


    //报单配送
    public function baodanpeisong($uid, $group_id)
    {

        $groupInfo = $this->groupInfo($group_id);

        $money = $groupInfo['bdps'];

        $data = array(

            'zhang_balance' => array('exp', "zhang_balance + $money")

        );

        $where = array('id' => $uid);

        $check = $this->users_model->editMember($where, $data);

        if ($check) {

            $this->trans_model->recorde($uid, $money, '购物币', '报单配送', '报单配送');

        }

    }


    //升级报单配送

    public function baodanpeisongUplevel($uid, $lsk)

    {

        $data = array(

            'zhang_balance' => array('exp', "zhang_balance + $lsk")

        );

        $where = array('id' => $uid);

        $check = $this->users_model->editMember($where, $data);

        if ($check) {

            $this->trans_model->recorde($uid, $lsk, '购物币', '升级报单配送', '升级报单配送');

        }

    }


    //销售奖（直推奖）

    public function xiaoshou($uid, $lsk, $shuomin = '')

    {

        //注册用户信息

        $userInfo = $this->users_model->getMemberInfoByID($uid);

        //注册用户会员组

        $groupInfo = $this->groupInfo($userInfo['group_id']);

        //推荐人信息

        $tjrInfo = $this->users_model->getMemberInfoByID($userInfo['rid']);

        $money = $groupInfo['tj'];

        //重复消费奖

        $cfxf_money = $this->cfxf($tjrInfo, $money, $shuomin, $userInfo['id'], $userInfo['username']);

        //税费

        $tax_money = $this->tax($tjrInfo, $money, $shuomin, $userInfo['id'], $userInfo['username']);

        //慈善基金

        $fund_money = $this->fund($tjrInfo, $money, $shuomin, $userInfo['id'], $userInfo['username']);

        //最终奖金

        $last_money = $money - $cfxf_money - $tax_money - $fund_money;

        if ($last_money > 0) {

            $check = $this->updateMemberMoney($tjrInfo['id'], 'ji', $last_money);

            if ($check) {

                //写入奖金来源记录

                $intro = $shuomin;

                if(!$this->recorde($tjrInfo['id'], $last_money, '积分', 1, $intro, $userInfo['id'], $userInfo['username'], $tjrInfo['username']))
                    return false;

                //写入本日结算记录

                if(!$this->bonuslog_model->log($tjrInfo['id'], $tjrInfo['username'], 1, $last_money))
                    return false;

            }

        }
return true;
    }


    //懒人奖（见点奖）

    public function jiandian($uid, $layer, $rel_uid, $rel_username)
    {
        $userInfo = $this->users_model->getMemberInfoByID($uid);

        $groupInfo = $this->groupInfo($userInfo['group_id']);

        $jiandian_money = $groupInfo['jiandian'];

        $jiandianlimit = $groupInfo['jiandianlimit'];

        $money = $this->jiandian_top($uid, $groupInfo, $jiandian_money);

        if ($money > 0) {

            if ($layer <= $jiandianlimit) {

                //重复消费奖

                $cfxf_money = $this->cfxf($userInfo, $money, '见点奖', $rel_uid, $rel_username);

                //税费

                $tax_money = $this->tax($userInfo, $money, '见点奖', $rel_uid, $rel_username);

                //慈善基金

                $fund_money = $this->fund($userInfo, $money, '见点奖', $rel_uid, $rel_username);

                //最终奖金

                $last_money = $money - $cfxf_money - $tax_money - $fund_money;

                if ($last_money > 0) {

                    $check = $this->updateMemberMoney($userInfo['id'], 'ji', $last_money);

                    if ($check) {

                        //写入奖金来源记录

                        $intro = '见点奖';
                        if(!$this->recorde($userInfo['id'], $last_money, '积分', 3, $intro, $rel_uid, $rel_username, $userInfo['username'])) {
                            return false;
                        }

                        //写入本日结算记录

                        if(!$this->bonuslog_model->log($userInfo['id'], $userInfo['username'], 3, $last_money))
                            return false;

                    }

                }

            }

        }

        $layer++;

        if ($userInfo['pid'] != 0) {

             $this->jiandian($userInfo['pid'], $layer, $rel_uid, $rel_username);
        }

    }


    //见点奖封顶

    private function jiandian_top($uid, $groupInfo, $money)

    {

        //该用户目前获得见点奖总额
        $res = $this->bonuslog_model->sum($uid, 'b3');
        $cur_jiandian_toal = $res['sum'];
        //已发放见点奖(扣前)
        $cur_jiandian_money_before = $cur_jiandian_toal / (1 - ($groupInfo['cfxf'] + $groupInfo['tax'] + $groupInfo['fund']) / 100);
        if ($groupInfo['jiandiantop'] > 0) {
            if ($money + $cur_jiandian_money_before > $groupInfo['jiandiantop']) {
                $money = $groupInfo['jiandiantop'] - $cur_jiandian_money_before;
                if ($money < 0) {
                    $money = 0;
                }
            }
        }
        return $money;

    }


    //对碰奖

    public function dpj($uid)

    {

        $condition = array(

            'l_count' => array('gt', 0),

            'r_count' => array('gt', 0)

        );

        $netList = $this->net_model->getNetList($condition);

        if ($netList) {

            foreach ($netList as $item) {

                $userInfo = $this->users_model->getMemberInfoByID($item['uid']);

                $groupInfo = $this->groupInfo($userInfo['group_id']);

                $y1 = $item['l_count'];

                $y2 = $item['r_count'];

                $dan = 0;

                if ($y1 >= $y2) {

                    $dan = $y2;

                    $y1 = $y1 - $y2;

                    $y2 = 0;

                } else {

                    $dan = $y1;

                    $y2 = $y2 - $y1;

                    $y1 = 0;

                }
                //判断是否在一代内或者没直推人
                $dpj_check = $this->dpj_check($uid, $item['uid'], $userInfo['group_id'], $y1, $y2);
                if (!$dpj_check) {
                    continue;
                }
                if ($dan > 0) {

                    $money_beofre = $groupInfo['dpj'];

                    //对碰奖日封顶

                    $money = $this->dpj_top($userInfo['id'], $userInfo['group_id'], $money_beofre);

                    if ($money > 0) {

                        //重复消费奖

                        $cfxf_money = $this->cfxf($userInfo, $money, 'PK奖');

                        //税费

                        $tax_money = $this->tax($userInfo, $money, 'PK奖');

                        //慈善基金

                        $fund_money = $this->fund($userInfo, $money, 'PK奖');

                        //最终奖金

                        $last_money = $money - $cfxf_money - $tax_money - $fund_money;

                        if ($last_money > 0) {

                            $check = $this->updateMemberMoney($userInfo['id'], 'ji', $last_money);

                            if ($check) {

                                //更新剩余业绩

                                $net_update_array['l_count'] = $y1;

                                $net_update_array['r_count'] = $y2;

                                $net_condition['uid'] = $item['uid'];

                                $this->net_model->editNet($net_condition, $net_update_array);

                                //写入奖金来源记录

                                $intro = 'PK奖';

                                if(!$this->recorde($userInfo['id'], $last_money, '积分', 2, $intro, 0, '-', $userInfo['username']))
                                    return false;

                                //写入本日结算记录

                                if(!$this->bonuslog_model->log($userInfo['id'], $userInfo['username'], 2, $last_money))
                                    return false;

                                //领导奖

                                //$puserInfo = $this->users_model->getMemberInfoByID($userInfo['pid']);

                                //$this->leadaward($userInfo['pid'], $money, 1);

                                //感恩奖

                                //$this->gej($userInfo['id'], $userInfo['username'], $userInfo['group_id'], $money);

                            }

                        }

                    }

                }

            }

        }
        return true;

    }

    //对碰奖判断是否在一代内或者没直推人并且是否在小区内
    private function dpj_check($uid, $dpj_uid, $dpj_group_id, $y1, $y2)
    {
        $groupInfo = $this->groupInfo($dpj_group_id);
        //$RecordTjr = $this->record_model->getRecordCount(array('tjr_id' => $dpj_uid));
        $user_lay_num = $this->net_model->getOneNetReField($uid, 'lay_num');
        $dpj_user_lay_num = $this->net_model->getOneNetReField($dpj_uid, 'lay_num');
        $inXiaoQu = $this->parent_model->getParentList(array('parent' => $dpj_uid, 'uid' => $uid));
        if ($inXiaoQu) {
            if ($user_lay_num - $dpj_user_lay_num >= $groupInfo['dpj_start_lay']) {
                return true;
            } else {
                //减去业绩
                $net_update_array['l_count'] = $y1;
                $net_update_array['r_count'] = $y2;
                $net_condition['uid'] = $dpj_uid;
                $this->net_model->editNet($net_condition, $net_update_array);
                return false;
            }
        } else {
            return false;
        }
    }

    //对碰奖日封顶

    private function dpj_top($uid, $group_id, $money)

    {

        $groupInfo = $this->groupInfo($group_id);

        $bdate = date('Y-m-d', time());

        $bonusInfo = $this->bonuslog_model->getOneByUidAndBdate($uid, $bdate);

        $cur_dpj_money = $bonusInfo['b2'];

        //已发放对碰奖(扣前)

        $cur_dpj_money_before = $cur_dpj_money / (1 - ($groupInfo['cfxf'] + $groupInfo['tax'] + $groupInfo['fund']) / 100);

        if ($money + $cur_dpj_money_before > $groupInfo['dpj_top']) {

            $money = $groupInfo['dpj_top'] - $cur_dpj_money_before;

            if ($money < 0) {

                $money = 0;

            }

        }

        return $money;

    }

    //层碰奖 每层只碰1次
    public function cpj()
    {
        $condition = array(
            'field' => '*,count(*) as num',
            'group' => 'uid,layer',
            'status' => 0
        );
        $result = $this->layerlist_model->getList($condition);
        foreach ($result as $item) {
            if ($item['num'] == 2) {
                $userInfo = $this->users_model->getMemberInfoByID($item['uid']);
                $groupInfo = $this->groupInfo($userInfo['group_id']);
                if ($item['layer'] <= $groupInfo['cpjlimit']) {
                    $cpj_money = $groupInfo['cpj'];
                    if ($cpj_money > 0) {
                        //重复消费奖
                        $cfxf_money = $this->cfxf($userInfo, $cpj_money, $item['layer'] . '代层碰奖');
                        //税费
                        $tax_money = $this->tax($userInfo, $cpj_money, $item['layer'] . '代层碰奖');
                        //慈善基金
                        $fund_money = $this->fund($userInfo, $cpj_money, $item['layer'] . '代层碰奖');
                        //最终奖金
                        $last_money = $cpj_money - $cfxf_money - $tax_money - $fund_money;
                        if ($last_money > 0) {
                            $check = $this->updateMemberMoney($userInfo['id'], 'ji', $last_money);
                            if ($check) {
                                $this->layerlist_model->editLayerlist(
                                    array('uid' => $userInfo['id'], 'layer' => $item['layer']),
                                    array('status' => 1)
                                );
                                //写入奖金来源记录
                                $intro = $item['layer'] . '代层碰奖';
                                $this->recorde($userInfo['id'], $last_money, '积分', 11, $intro, 0, '-', $userInfo['username']);
                                //写入本日结算记录
                                $this->bonuslog_model->log($userInfo['id'], $userInfo['username'], 11, $last_money);
                            }
                        }
                    }
                }
            }
        }
        return true;
    }

    //培育奖

    public function leadaward($uid, $money, $layer)

    {

        $userInfo = $this->users_model->getMemberInfoByID($uid);

        $groupInfo = $this->groupInfo($userInfo['group_id']);

        if ($layer <= $groupInfo['leadlimit']) {

            $lead_money = $groupInfo['lead'] / 100 * $money;

            if ($lead_money > 0) {

                //重复消费奖

                $cfxf_money = $this->cfxf($userInfo, $lead_money, $layer . '代培育奖');

                //税费

                $tax_money = $this->tax($userInfo, $lead_money, $layer . '代培育奖');

                //慈善基金

                $fund_money = $this->fund($userInfo, $lead_money, $layer . '代培育奖');

                //最终奖金

                $last_money = $lead_money - $cfxf_money - $tax_money - $fund_money;

            }

            if ($last_money > 0) {

                $check = $this->updateMemberMoney($userInfo['id'], 'ji', $last_money);

                if ($check) {

                    //写入奖金来源记录

                    $intro = $layer . '代培育奖';

                    $this->recorde($userInfo['id'], $last_money, '积分', 4, $intro, 0, '-', $userInfo['username']);

                    //写入本日结算记录

                    $this->bonuslog_model->log($userInfo['id'], $userInfo['username'], 4, $last_money);

                }

            }

        }

        if ($userInfo['pid'] != 0) {

            $layer++;

            $this->leadaward($userInfo['pid'], $money, $layer);

        }

    }


    /**服务站补贴
     * @param $ssid 服务站id
     * @param $lsk  投资金额
     * @param $rel_uid 关联会员ID
     * @param $reL_username 关联会员名
     */

    public function ssSubsidy($ssid, $rel_uid, $reL_username, $lsk)

    {

        $ssInfo = $this->biz_model->getOneBiz($ssid);

        if ($ssInfo['status'] == 1) {

            $userInfo = $this->users_model->getMemberInfoByID($ssInfo['uid']);

            $groupInfo = $this->groupInfo($userInfo['group_id']);

            //$subsidy_money = round($groupInfo['subsidy'] / 100 * $lsk, 2);
            $subsidy_money = $groupInfo['subsidy'];

            if ($userInfo && $subsidy_money > 0) {

                $check = $this->updateMemberMoney($userInfo['id'], 'ji', $subsidy_money);

                if ($check) {

                    //写入奖金来源记录

                    $intro = '服务站补贴';
                    if(!$this->recorde($userInfo['id'], $subsidy_money, '积分', 5, $intro, $rel_uid, $reL_username, $userInfo['username'])){
                        return false;

                    }


                    //写入本日结算记录

                    if(!$this->bonuslog_model->log($userInfo['id'], $userInfo['username'], 5, $subsidy_money)){
                        return false;
                    }


            }
                else{

                    return false;
                }

            }

        }

        return true;

    }


    //服务站推荐奖

    public function ssTuijian($uid)

    {

        $userInfo = $this->users_model->getMemberInfoByID($uid);

        $rel_uid = $userInfo['id'];

        $rel_username = $userInfo['username'];

        $rid = $userInfo['rid'];

        $ruserInfo = $this->users_model->getMemberInfoByID($rid);

        $setting_array = $this->setting_model->getRowSetting('con_ss_tuijian_rate');

        $con_ss_tuijian_rate = $setting_array['value'];

        $ssInfo = $this->biz_model->getOneBiz($userInfo['ssid']);

        if ($rid != 0) {

            $ssTuijian_money = $con_ss_tuijian_rate / 100 * $userInfo['lsk'];

            if ($ssTuijian_money > 0) {

                //重复消费奖

                $cfxf_money = $this->cfxf($ruserInfo, $ssTuijian_money, '服务站推荐奖', $rel_uid, $rel_username);

                //税费

                $tax_money = $this->tax($ruserInfo, $ssTuijian_money, '服务站推荐奖', $rel_uid, $rel_username);

                //慈善基金

                $fund_money = $this->fund($ruserInfo, $ssTuijian_money, '服务站推荐奖', $rel_uid, $rel_username);

                //最终奖金

                $last_money = $ssTuijian_money - $cfxf_money - $tax_money - $fund_money;

                if ($last_money > 0) {

                    $check = $this->updateMemberMoney($ruserInfo['id'], 'ji', $last_money);

                    if ($check) {

                        //写入奖金来源记录

                        $intro = '服务站推荐奖';

                        $this->recorde($ruserInfo['id'], $last_money, '积分', 6, $intro, $rel_uid, $rel_username, $ruserInfo['username']);

                        //写入本日结算记录

                        $this->bonuslog_model->log($ruserInfo['id'], $ruserInfo['username'], 6, $last_money);

                    }

                }

            }

        }

    }


    //感恩奖

    public function gej($uid, $username, $group_id, $money)

    {

        $groupInfo = $this->groupInfo($group_id);

        $gej_money = round($groupInfo['gej'] / 100 * $money, 2);

        $condition = array(

            'rid' => $uid,

            'status' => 1

        );

        $userList = $this->users_model->getMemberList($condition);

        $userNum = count($userList);

        if ($userNum > 0) {

            $gej_money_avg = round($gej_money / $userNum, 2);

        }

        if ($userList && $gej_money_avg > 0) {

            foreach ($userList as $item) {

                //重复消费奖

                $cfxf_money = $this->cfxf($item, $gej_money_avg, '感恩奖', $uid, $username);

                //税费

                $tax_money = $this->tax($item, $gej_money_avg, '感恩奖', $uid, $username);

                //慈善基金

                $fund_money = $this->fund($item, $gej_money_avg, '感恩奖', $uid, $username);

                //最终奖金

                $last_money = $gej_money_avg - $cfxf_money - $tax_money - $fund_money;

                if ($last_money > 0) {

                    $check = $this->updateMemberMoney($item['id'], 'ji', $last_money);

                    if ($check) {

                        //写入奖金来源记录

                        $intro = '感恩奖';

                        $this->recorde($item['id'], $last_money, '积分', 10, $intro, $uid, $username, $item['username']);

                        //写入本日结算记录

                        $this->bonuslog_model->log($item['id'], $item['username'], 10, $last_money);

                    }

                }

            }

        }

    }

    //分润工资
    public function share($star, $money)
    {
        $userList = $this->users_model->getMembersList(array('star' => $star));
        if ($money > 0) {
            foreach ($userList as $item) {
                $uid = $item['id'];
                $username = $item['username'];
                //重复消费奖
                $cfxf_money = $this->cfxf($item, $money, '分润工资', $uid, $username);
                //税费
                $tax_money = $this->tax($item, $money, '分润工资', $uid, $username);
                //慈善基金
                $fund_money = $this->fund($item, $money, '分润工资', $uid, $username);
                //最终奖金
                $last_money = $money - $cfxf_money - $tax_money - $fund_money;
                if ($last_money > 0) {
                    $check = $this->updateMemberMoney($item['id'], 'ji', $last_money);
                    if ($check) {
                        //写入奖金来源记录
                        $intro = '分润工资';
                        $this->recorde($item['id'], $last_money, '积分', 12, $intro, 0, '-', $item['username']);
                        //写入本日结算记录
                        $this->bonuslog_model->log($item['id'], $item['username'], 12, $last_money);
                    }
                }
            }
        }
        return $check;
    }

    //重复消费奖

    private function cfxf($userinfo, $money, $beizhu, $rel_uid = 0, $rel_username = '-')

    {

        $groupInfo = $this->groupInfo($userinfo['group_id']);

        $bili = $groupInfo['cfxf'] / 100;

        $cfxf_money = round($bili * $money, 2);

        if ($cfxf_money > 0) {

            //更新奖金

            $check = $this->updateMemberMoney($userinfo['id'], 'zhang', $cfxf_money);

            if ($check) {

                //写入奖金来源记录

                $intro = $beizhu . '的' . $bili * 100 . '%,进入购物币账户';

                if(!$this->recorde($userinfo['id'], $cfxf_money, '购物币', 7, $intro, $rel_uid, $rel_username, $userinfo['username']))
                    return false;

                //写入本日结算记录

                if(!$this->bonuslog_model->log($userinfo['id'], $userinfo['username'], 7, $cfxf_money))
                    return false;

                return $cfxf_money;

            }

        }

        return 0;

    }


    //税费

    private function tax($userinfo, $money, $beizhu, $rel_uid = 0, $rel_username = '-')

    {

        $groupInfo = $this->groupInfo($userinfo['group_id']);

        $bili = $groupInfo['tax'] / 100;

        $tax_money = round($bili * $money, 2);

        if ($tax_money > 0) {

            //写入奖金来源记录

            $intro = '扣除' . $beizhu . $bili * 100 . '%的税费';

            if(!$this->recorde($userinfo['id'], $tax_money, '税费', 8, $intro, $rel_uid, $rel_username, $userinfo['username']))
                return false;

            //写入本日结算记录

            if(!$this->bonuslog_model->log($userinfo['id'], $userinfo['username'], 8, $tax_money))
                return false;

            return $tax_money;

        }

        return 0;

    }


    //慈善基金

    private function fund($userinfo, $money, $beizhu, $rel_uid = 0, $rel_username = '-')

    {

        $groupInfo = $this->groupInfo($userinfo['group_id']);

        $bili = $groupInfo['fund'] / 100;

        $fund_money = round($bili * $money, 2);

        if ($fund_money > 0) {

            //写入奖金来源记录

            $intro = '扣除' . $beizhu . '的' . $bili * 100 . '%,进入慈善基金';

            if(!$this->recorde($userinfo['id'], $fund_money, '慈善基金', 9, $intro, $rel_uid, $rel_username, $userinfo['username']))
                return false;

            //写入本日结算记录

            if(!$this->bonuslog_model->log($userinfo['id'], $userinfo['username'], 9, $fund_money))
                return false;

            return $fund_money;

        }


        return 0;

    }


    /**更新用户各类奖金
     * @param $uid
     * @param $type bao报单币 ji积分 zhang购物币
     */

    private function updateMemberMoney($uid, $type, $money)

    {

        $where = array(

            'id' => $uid,

        );

        $data = array(

            $type . '_balance' => array('exp', $type . "_balance + $money")

        );

        $result = $this->users_model->editMember($where, $data);

        if ($result) {

            $userInfo = $this->users_model->getMemberInfoByID($uid);

            if ($userInfo['paynetfee'] == 0) {

                $setting_array = $this->setting_model->getRowSetting('con_net_fee');

                $con_net_fee = $setting_array['value'];

                if ($userInfo['ji_balance'] >= $con_net_fee && $con_net_fee > 0) {

                    $where = array(

                        'id' => $uid,

                    );

                    $data = array(

                        'ji_balance' => array('exp', "ji_balance-$con_net_fee"),

                        'paynetfee' => 1

                    );

                    $check = $this->users_model->editMember($where, $data);

                    if ($check) {

                        $num = '-' . $con_net_fee;

                        $this->trans_model->recorde($uid, $num, '积分', '扣除' . $con_net_fee . '网络费', '扣除' . $con_net_fee . '网络费');

                    }

                }

            }

        }

        return $result;

    }


    //奖励来源记录 $type 1销售奖 2对碰奖 3懒人奖(见点奖) 4培育奖（领导奖） 5服务站补贴 6服务站推荐奖 7重复消费 8税费 9慈善基金 10感恩奖 11层碰奖 12分润工资

    private function recorde($uid, $num, $money_type, $type, $intro = '', $rel_uid, $rel_username, $username)

    {

       return $this->bonuslaiyuan_model->recorde($uid, $num, $money_type, $type, $intro, $rel_uid, $rel_username, $username);

    }


    private function groupInfo($group_id)

    {

        foreach ($this->group as $item) {

            if ($item['group_id'] == $group_id) {

                return $item;

            }

        }

    }


}