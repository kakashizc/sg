<?php
/**
 * 前台登录 退出操作
 *
 **/
defined('InShopBN') or exit('Access Invalid!');

class loginControl extends BaseHomeControl
{

    public function __construct()
    {
        parent::__construct();
        Tpl::output('hidden_nctoolbar', 1);
    }

    /**
     * 登录操作
     *
     */
    public function indexOp()
    {
        Language::read("home_login_index");
        $lang = Language::getLangContent();
        $model_member = Model('member');
        //检查登录状态
        $model_member->checkloginMember();

        if ($_GET['inajax'] == 1 && C('captcha_status_login') == '1') {
            $script = "document.getElementById('codeimage').src='" . APP_SITE_URL . "/index.php?act=seccode&op=makecode&nchash=" . getNchash() . "&t=' + Math.random();";
        }
        $result = chksubmit(true, C('captcha_status_login'), 'num');
        if ($result !== false) {
            if ($result === -11) {
                showDialog($lang['login_index_login_illegal'], '', 'error', $script);
            } elseif ($result === -12) {
                showDialog($lang['login_index_wrong_checkcode'], '', 'error', $script);
            }
            if (process::islock('login')) {
                showDialog($lang['nc_common_op_repeat'], SHOP_SITE_URL, '', 'error', $script);
            }
            $obj_validate = new Validate();
            $obj_validate->validateparam = array(
                array("input" => $_POST["username"], "require" => "true", "message" => $lang['login_index_username_isnull']),
                array("input" => $_POST["pwd"], "require" => "true", "message" => $lang['login_index_password_isnull']),
            );
            $error = $obj_validate->validate();
            if ($error != '') {
                showDialog($error, SHOP_SITE_URL, 'error', $script);
            }
            $array = array();
            $array['username'] = $_POST['username'];
            $array['password'] = md5($_POST['pwd']);
            $member_info = $model_member->getMemberInfo($array);
            if (is_array($member_info) and !empty($member_info)) {
                if (!$member_info['member_state']) {
                    //showDialog($lang['login_index_account_stop'],''.'error',$script);
                }
            } else {
                process::addprocess('login');
                showDialog($lang['login_index_login_fail'], '', 'error', $script);
            }
            $model_member->createSession($member_info);
            process::clear('login');

            if ($_GET['inajax'] == 1) {
                showDialog('', $_POST['ref_url'] == '' ? 'reload' : $_POST['ref_url'], 'js');
            } else {
                redirect($_POST['ref_url']);
            }
        } else {

            //登录表单页面
            $_pic = @unserialize(C('login_pic'));
            if ($_pic[0] != '') {
                Tpl::output('lpic', UPLOAD_SITE_URL . '/' . ATTACH_LOGIN . '/' . $_pic[array_rand($_pic)]);
            } else {
                Tpl::output('lpic', UPLOAD_SITE_URL . '/' . ATTACH_LOGIN . '/' . rand(1, 4) . '.jpg');
            }

            if (empty($_GET['ref_url'])) {
                $ref_url = getReferer();
                if (!preg_match('/act=login&op=logout/', $ref_url)) {
                    $_GET['ref_url'] = $ref_url;
                }
            }
            Tpl::output('html_title', C('site_name'));
            if ($_GET['inajax'] == 1) {
                Tpl::showpage('login_inajax', 'null_layout');
            } else {
                Tpl::showpage('login');
            }
        }
    }

    //取得密保
    public function getmbOp()
    {
        $username = trim($_REQUEST['uname']);

        $model_member = Model('member');
        $array = array();
        $array['username'] = $username;

        $res = array();

        $member_info = $model_member->getMemberInfo($array);
        if (is_array($member_info) and !empty($member_info)) {
            $res = array('status' => 1, 'info' => $member_info['problem']);
        } else {
            $res = array('status' => 0, 'info' => '无此用户');
        }

        exit(json_encode($res));
    }

    //邮箱找回
    public function forgetOp()
    {
        $email = $_REQUEST['email'];
        $username = $_REQUEST['uname'];
        $typ = $_REQUEST['typ'];

        $model_member = Model('member');
        $array['username'] = $username;
        $UserInfo = $model_member->getMemberInfo($array);

        $res = array('info' => '用户名和邮箱不匹配!请查询后重试', 'status' => 0);

        if (is_array($UserInfo) and !empty($UserInfo)) {
            if ($typ == "mb") {
                if ($email == $UserInfo['answer']) {
                    $pass = rand(1000, 9999);

                    $pwd = md5($pass);
                    $data = array('password' => $pwd, 'jy_pwd' => $pwd);
                    $model_member->editMember($array, $data);
                    $res = array('info' => '您的新密码是：' . $pass . '，请登陆后立即修改', 'status' => 1);
                } else {
                    $res = array('info' => '出错了', 'status' => 0);
                }

                exit(json_encode($res));
            }

            $token = Model('token');

            if ($UserInfo['email'] == '') {
                $res = array('info' => '该用户尚未设置邮箱,请联系管理员重置密码', 'status' => 0);
                exit(json_encode($res));
            }

            $domain = $_SERVER['HTTP_HOST'];
            $url = "http://$domain/index.php?act=login&op=find&token=";

            $num = rand(1, 3);
            $data = array(
                'id' => $UserInfo['id'],
                'userid' => $UserInfo['id'],
                'token' => md5($UserInfo['username'] . $UserInfo['email'] . $num),
                'status' => 0,
                'time' => time(),
                'num' => $num,
            );
            $url = "http://$domain/index.php?act=login&op=find&token=" . $data['token'];
            $str = '亲爱的用户' . $UserInfo['username'] . '<br>您申请了找回密码，请点击下面的链接重置密码：<br/><a href="' . $url . '">' . $url . '</a><br>------------------------------<br/>如果您点击上述链接无效，请把下面的代码拷贝到浏览器的地址栏中<br/>' . $url . '<br/>本链接在您验证过一次后将自动失效';
            $email = new Email();

            if ($token->getTokenByID($UserInfo['id'])) {
                $result = $email->send_sys_email($UserInfo['email'], '为众会员---找回密码', $str);
                if (!$result) {
                    $res = array('info' => '邮件发送失败,清稍后重试', 'status' => 0);
                    exit(json_encode($res));
                }
                $r = $token->editToken(array('id' => $UserInfo['id']), $data);

                $res = array('info' => '发送成功!请查阅邮箱!', 'status' => 1);
                exit(json_encode($res));
            } else {
                $result = $email->send_sys_email($UserInfo['email'], '为众会员---找回密码', $str);
                if (!$result) {
                    $res = array('info' => '邮件发送失败,清稍后重试', 'status' => 0);
                    exit(json_encode($res));
                }
                $token->addToken($data);
                $res = array('info' => '发送成功!请查阅邮箱!', 'status' => 1);
                exit(json_encode($res));
            }

        } else {
            exit(json_encode($res));
        }
    }

    public function findOp()
    {
        $tn = $_REQUEST['token'];
        $token = Model('token');

        $map = array(
            'token' => $tn,
            'status' => 0,
        );
        $TokenInfo = $token->getToken($map);
        if (!$TokenInfo) {
            showMessage('链接已失效...', 'index.php', 'html', 'error');
            exit();
        }
        Tpl::output('tn', $tn);
        $token->editToken(array('id' => $TokenInfo['id']), array('status' => 1));
        Tpl::showpage('login_find_token');
    }

    public function doRepwdOp()
    {
        $tn = $_REQUEST['token'];
        $pwd = md5($_REQUEST['newpwd']);
        $token = Model('token');
        $user = Model('member');
        $map = array(
            'token' => $tn,
        );
        $TokenInfo = $token->getToken($map);
        if (!$TokenInfo) {
            $res = array('info' => '该用户不存在', 'status' => 0);
            exit(json_encode($res));
        }
        $user->editMember(array('id' => $TokenInfo['id']), array('password' => $pwd));
        $res = array('info' => '重置密码成功,请使用新密码进行登录!', 'status' => 1);
        exit(json_encode($res));
    }

    /**
     * 退出操作
     *
     * @param int $id 记录ID
     * @return array $rs_row 返回数组形式的查询结果
     */
    public function logoutOp()
    {
        Language::read("home_login_index");
        $lang = Language::getLangContent();
        // 清理消息COOKIE
        setBnCookie('is_login', '', -3600);
        unset ($_SESSION['pass_jy_pwd']);
        if (empty($_GET['ref_url'])) {
            $ref_url = getReferer();
        } else {
            $ref_url = $_GET['ref_url'];
        }
        redirect('index.php?act=login&ref_url=' . urlencode($ref_url));
    }


    /**
     * 会员名称检测
     *
     * @param
     * @return
     */
    public function check_memberOp()
    {
        /**
         * 实例化模型
         */
        $model_member = Model('users');

        $check_member_name = $model_member->getMemberInfo(array('username' => $_GET['user_name']));
        if (is_array($check_member_name) and count($check_member_name) > 0) {
            echo 'false';
        } else {
            echo 'true';
        }
    }
}