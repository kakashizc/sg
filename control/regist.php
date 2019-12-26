<?php
header("Content-type: text/html; charset=utf-8");
defined('InShopBN') or exit('Access Invalid!');

class registControl extends BaseMemberControl{


    private $group_model;

    private $users_model;

    private $net_model;

    private $biz_model;

    private $record_model;

    private $bonus_model;


    public function __construct()
    {

        parent::__construct();

        $this->group_model = Model('group');

        $this->users_model = Model('member');

        $this->net_model = Model('net');

        $this->biz_model = Model('biz');

        $this->record_model = Model('record');

        $this->bonus_model = Model('bonus');


        Tpl::output('Team_index_active', "active");

    }

    public function toregistOp(){
        //根据id,找到对应的username,
        $username = $this->users_model->getMemberInfo(array('id'=>$_GET['pid']),'username');
        if($username){
            Tpl::output('pid',$username['username']);
            Tpl::showpage('team.regist');
        }else{
            $this->error('用户不存在');
        }

    }


    public function uploadOp()
    {
        $arr = array();
        $file = $_FILES['file'];
        $side = $_POST['side'];//身份证正反面
        $allowedExts = array("jpeg", "jpg", "png");
        $temp = explode(".", $_FILES["file"]["name"]);
        $extension = end($temp);// 获取文件后缀名
        if ( ($_FILES["file"]["type"] == "image/jpeg" || $_FILES["file"]["type"] == "image/jpg" || $_FILES["file"]["type"] == "image/png") && ($_FILES["file"]["size"] < 10480000) && in_array($extension, $allowedExts))
        {
            $path = 'data/upload/indentity/';
            if(!is_dir($path)){
                mkdir($path);
            }
            $filename = mt_rand(10,100).time();
            $name = $path.$filename.'.jpg';
            move_uploaded_file($file['tmp_name'],$name);
            //调用百度api, 审核身份证是否合法
            $local_path = BASE_PATH.DS.$name;
            $res = $this->check_indentity($local_path,$side);

            if($res == 'ok'){
                $arr['code'] = 0;
                $arr['msg'] = "http://".$_SERVER['SERVER_NAME'].DS.$name;
                echo json_encode($arr);
            }else{
                $arr['code'] = 1;
                $arr['msg'] = $res;
                unlink($local_path);
                echo json_encode($arr);
            }

        }else{
            $arr['code'] = 1;
            $arr['msg'] =  '图片格式错误或超过10M';
            echo json_encode($arr);
        }
    }

    /**
     * 调用百度api,检测合法性
     * @param $img string 图片本地路径
     * @param $side string 图片正面, front / back
     * */
    public function check_indentity($img,$side)
    {
        require_once 'AipOcr.php';
        $client = new AipOcr('18099739', '1NodzLliaLbyG9Kymr6ZHsGW', 'UrNTqssUyMf2lMtPnmLypTvAGtbtzG4s');
        $image = file_get_contents($img);
        $idCardSide = $side;
        // 调用身份证识别
        $client->idcard($image, $idCardSide);

        // 如果有可选参数
        $options = array();
        $options["detect_direction"] = "true";
        $options["detect_risk"] = "false";

        // 带参数调用身份证识别
        $res = $client->idcard($image, $idCardSide, $options);
        $msg = '';
        if( $res['image_status'] == 'normal' ){
            return $msg = 'ok';
        }else{
            switch ( $res['image_status'] ){
                case 'reversed_side':
                    $msg =  '未摆正身份证';
                    break;
                case 'non_idcard':
                    $msg =  '上传的图片中不包含身份证';
                    break;
                case 'blurred':
                    $msg = '身份证模糊';
                    break;
                case 'over_exposure':
                    $msg =  '身份证关键字段反光或过曝';
                    break;
                case 'unknown':
                    $msg =  '未知状态';
                    break;
            }
            return $msg;
        }
    }


    public function do_registerOp()
    {
        $yhm = trim($_REQUEST['yhm']);
        $dlmm = trim($_REQUEST['dlmm']);
        $jymm = trim($_REQUEST['jymm']);
        $group_id = $_REQUEST['group_id'];
        $email = $_REQUEST['email'];
        $tjr = trim($_REQUEST['tjr']);
        $jdr = trim($_REQUEST['jdr']);
        $scwz = $_REQUEST['scwz'];
        $sfzh = $_REQUEST['sfzh'];
        $dz = $_REQUEST['dz'];
        $tel = $_REQUEST['tel'];
        $qq = $_REQUEST['qq'];
        $name = $_REQUEST['name'];
        $sex = $_REQUEST['sex'];
        $ssname = $_REQUEST['ssname'];
        $users = Model("member");

        /**
         * 验证
         */
        $obj_validate = new Validate();
        $obj_validate->validateparam = array(

            array("input" => $_REQUEST["name"], "require" => "true", 'validator' => 'chinese', "message" => '真实姓名必须为中文')
        );
        $error = $obj_validate->validate();
        if ($error) {
            $this->error($error);
        }

        $have_username = $this->check_username($yhm);
        if (!$have_username) {
            $this->error('用户名重复！');
        }

        //验证推荐人是否存在
        $tjr_info = $this->select_tjr($tjr);
        //验证接点人
        $jdr_uid = $this->check_wz($jdr, $scwz);
        //验证服务站是否存在并且启用
        $ssInfo = $this->select_ss($ssname);
        //报单费
        $lsk = $this->group_model->getOneGroupReField($group_id, 'lsk');

        //添加用户到表
        $data = array(
            'username' => $yhm,
            'password' => md5(trim($dlmm)),
            'jy_pwd' => md5(trim($jymm)),
            'status' => 0,
            'time' => time(),
            'level_id' => 1,
            'num' => 0,
            'login_status' => 0,
            'login_time' => time(),
            'name' => $name,
            //'problem' => $mbwt,
            //'answer' => $mbda,
            'idcard' => $sfzh,
            'address' => $dz,
            'tel' => $tel,
            'qq' => $qq,
            'email' => $email,
            'sex' => $sex,
            'rid' => $tjr_info['id'],
            'pid' => $jdr_uid,
            'ssid' => $ssInfo['id'],
            'ssuid' => $ssInfo['uid'],
            'ssname' => $ssname,
            'group_id' => $group_id,
            'rname' => $tjr_info['username'],
            'lsk' => $lsk
        );

        $res = $users->addMember($data);
        if ($res) {
            $check = $users->register(array('uid' => $res, 'pid' => $jdr_uid, 'tjr' => $tjr_info, 'scwz' => $scwz, 'userid' => $this->userid, 'lsk' => $lsk));
        }
        if ($check) {
            $this->success('注册成功');
        } else {
            $this->error('注册用户失败，请刷新后重试');
        }
    }

    //检查生成username是否重复

    private function check_username($username)
    {
        $have = $this->users_model->getMemberInfo(array('username' => $username));
        if ($have) {
            return false;
        }
        return true;

    }

    //检测用户是否存在
    private function select_tjr($tjr)
    {
        $net = Model('net');
        $re = $net->select_tjr($tjr);
        if ($re) {
            return $re;
        } else {
            $this->error('推荐人不存在,请检查!');
        }
    }


    //检测用户下级
    private function check_wz($username, $wz)
    {
        $net = Model('net');
        $info = $net->select_wz($username);
        if ($info == 0) {
            $this->error('该接点用户尚未激活,不能添加下级');
        } elseif ($info == -1) {
            $this->error('该接点用户不存在,请检查');
        } else {
            if ($wz == 1) {
                if (empty($info['l_id'])) {
                    return $info['uid'];
                } else {
                    $this->error('该接点用户的市场位置左区已满!请重新选择');
                }
            } else {
                if (empty($info['r_id'])) {
                    return $info['uid'];
                } else {
                    $this->error('该接点用户的市场位置右区已满!请重新选择');
                }
            }
        }
    }

    //检查服务站是否存在
    private function select_ss($name)
    {
        $re = $this->biz_model->getOneBizByUsername($name);
        if ($re) {
            if ($re['is_show']) {
                return $re;
            } else {
                $this->error('该服务站未启用！请更换服务站');
            }
        } else {
            $this->error('服务站不存在,请检查!');
        }
        exit;
    }

}