<?php
/**
 * 设置
 *
 */


defined('InShopBN') or exit('Access Invalid!');

class csControl extends SystemControl
{
    private $links = array(
        array('url' => 'act=cs&op=param', 'lang' => 'upload_param'),
        array('url' => 'act=cs&op=group', 'lang' => 'group_param'),
    );
    private $group_model;

    public function __construct()
    {
        parent::__construct();
        Language::read('setting');
        $this->group_model = Model('group');
    }

    public function indexOp()
    {
        $this->paramOp();
    }

    /**
     * 上传参数设置
     *
     */
    public function paramOp()
    {
        $data_serialize = array(
            'con_star'
        );
        if (chksubmit()) {

            $model_setting = Model('setting');

            $data = array();
            $input = array();
            //上传图片
            $upload = new UploadFile();
            $upload->set('default_dir', ATTACH_PATH . '/payimg');
            $upload->set('thumb_ext', '');
            $upload->set('file_name', 'alipayimg.jpg');
            $upload->set('ifremove', false);
            if (!empty($_FILES['con_aliimg_account']['name'])) {
                $result = $upload->upfile('con_aliimg_account');
                if (!$result) {
                    showMessage($upload->error, '', '', 'error');
                } else {
                    $input['con_aliimg_account'] = $upload->file_name;
                }
            } elseif ($_POST['old_con_aliimg_account'] != '') {
                $input['con_aliimg_account'] = 'alipayimg.jpg';
            }

            $upload->set('default_dir', ATTACH_PATH . '/payimg');
            $upload->set('thumb_ext', '');
            $upload->set('file_name', 'wxpayimg.jpg');
            $upload->set('ifremove', false);
            if (!empty($_FILES['con_wximg_account']['name'])) {
                $result = $upload->upfile('con_wximg_account');
                if (!$result) {
                    showMessage($upload->error, '', '', 'error');
                } else {
                    $input['con_wximg_account'] = $upload->file_name;
                }
            } elseif ($_POST['old_con_wximg_account'] != '') {
                $input['con_wximg_account'] = 'wxpayimg.jpg';
            }
            foreach ($_POST as $key => $val) {
                if (in_array($key, $data_serialize)) {
                    $data[$key] = serialize($val);
                } else {
                    $data[$key] = trim($val);
                }
            }
            foreach ($input as $key => $val) {
                $data[$key] = trim($val);
            }
            $result = $model_setting->updateSetting($data);
            if ($result) {
                $this->log(L('nc_edit,upload_param'), 1);
                showMessage(L('nc_common_save_succ'));
            } else {
                $this->log(L('nc_edit,upload_param'), 0);
                showMessage(L('nc_common_save_fail'));
            }
        }

        //获取默认图片设置属性
        $model_setting = Model('setting');
        $list_setting = $model_setting->getListSetting();

        foreach ($list_setting as $key => $value) {
            if (in_array($key, $data_serialize)) {
                $list_setting[$key] = unserialize($value);
            }
        }
        Tpl::output('list_setting', $list_setting);

        //输出子菜单
        Tpl::output('top_link', $this->sublink($this->links, 'param'));
        //
        Tpl::setDirquna('system');
        Tpl::showpage('upload.param');
    }

    /**
     * 会员等级设置
     */
    public function groupOp()
    {
        //输出子菜单
        Tpl::output('top_link', $this->sublink($this->links, 'group'));
        //
        Tpl::setDirquna('system');
        Tpl::showpage('group.index');
    }

    /**
     * 会员异步
     */
    public function groupGetXmlOp()
    {
        $condition = array();
        if (!empty($_POST['qtype'])) {
            $condition['typeid'] = intval($_POST['qtype']);
        }
        if (!empty($_POST['query'])) {
            $condition['like_title'] = $_POST['query'];
        }
        if (!empty($_POST['sortname']) && in_array($_POST['sortorder'], array('asc', 'desc'))) {
            $condition['order'] = $_POST['sortname'] . ' ' . $_POST['sortorder'];
        }
        $condition['order'] = ltrim($condition['order'] . ',group_id desc', ',');
        $page = new Page();
        $page->setEachNum(intval($_POST['rp']));
        $page->setStyle('admin');
        $group_list = $this->group_model->getGroupList($condition, $page);
        $data = array();
        $data['now_page'] = $page->get('now_page');
        $data['total_num'] = $page->get('total_num');
        if (is_array($group_list)) {
            foreach ($group_list as $k => $v) {
                $list = array();
                $list['operation'] = "<a class='btn blue' href='index.php?act=cs&op=groupEdit&group_id={$v['group_id']}'><i class='fa fa-pencil-square-o'></i>编辑</a>";
                if ($v['status'] == 1) {
                    $list['operation'] .= "<a class='btn blue' href='index.php?act=cs&op=trunOff&status=0&id=" . $v['group_id'] . "'>
                <i class='fa fa-pause'></i>停用</a>";
                } elseif ($v['status'] == 0) {
                    $list['operation'] .= "<a class='btn blue' href='index.php?act=cs&op=trunOff&status=1&id=" . $v['group_id'] . "'>
                <i class='fa fa-play'></i>启用</a>";
                }
                $list['name'] = $v['name'];
                $list['lsk'] = $v['lsk'];
                //$list['bdps'] = $v['bdps'];
                $list['tj'] = $v['tj'];
                $list['dpj'] = $v['dpj'];
                $list['dpj_top'] = $v['dpj_top'];
                $list['cpj'] = $v['cpj'];
                $list['jiandian'] = $v['jiandian'];
                //$list['lead'] = $v['lead'];
                //$list['gej'] = $v['gej'];
                $list['subsidy'] = $v['subsidy'];
                $list['cfxf'] = $v['cfxf'];
                //$list['tax'] = $v['tax'];
                //$list['fund'] = $v['fund'];
                $data['list'][$v['group_id']] = $list;
            }
        }
        exit(Tpl::flexigridXML($data));
    }

    public function trunOffOp()
    {
        $url = 'index.php?act=cs';
        $id = intval($_GET['id']) ? intval($_GET['id']) : showMessage('参数错误！', $url, 'html', 'error');
        $status = intval($_GET['status']);
        $result = $this->group_model->updates(array('group_id' => $id, 'status' => $status));
        if ($result) {
            showMessage('操作成功！');
        } else {
            showMessage('操作失败！', $url, 'html', 'error');
        }
    }

    /**
     * 编辑会员
     */
    public function groupEditOp()
    {
        $group_id = $_GET['group_id'];
        if (chksubmit()) {
            $data = array();
            foreach ($_POST as $key => $val) {
                $data[$key] = trim($val);
            }
            unset($data['form_submit']);
            unset($data['ref_url']);
            $result = $this->group_model->updates($data);
            if ($result) {
                $this->log(L('nc_edit,upload_param'), 1);
                showMessage(L('nc_common_save_succ'));
            } else {
                $this->log(L('nc_edit,upload_param'), 0);
                showMessage(L('nc_common_save_fail'));
            }
        }
        $group_array = $this->group_model->getOneGroup($group_id);
        Tpl::output('PHPSESSID', session_id());
        //Tpl::output('parent_list', $parent_list);
        Tpl::output('group_array', $group_array);
        Tpl::setDirquna('system');
        Tpl::showpage('group.edit');
    }

    /**
     * 默认图设置
     */
    public function default_thumbOp()
    {
        $model_setting = Model('setting');
        if (chksubmit()) {
            //上传图片
            $upload = new UploadFile();
            $upload->set('default_dir', ATTACH_COMMON);
            //默认会员头像
            if (!empty($_FILES['default_user_portrait']['tmp_name'])) {
                $thumb_width = '32';
                $thumb_height = '32';

                $upload->set('thumb_width', $thumb_width);
                $upload->set('thumb_height', $thumb_height);
                $upload->set('thumb_ext', '_small');
                $upload->set('file_name', '');
                $result = $upload->upfile('default_user_portrait');
                if ($result) {
                    $_POST['default_user_portrait'] = $upload->file_name;
                } else {
                    showMessage($upload->error, '', '', 'error');
                }
            }
            $list_setting = $model_setting->getListSetting();
            $update_array = array();
            if (!empty($_POST['default_user_portrait'])) {
                $update_array['default_user_portrait'] = $_POST['default_user_portrait'];
            }
            if (!empty($update_array)) {
                $result = $model_setting->updateSetting($update_array);
            } else {
                $result = true;
            }
            if ($result === true) {
                //判断有没有之前的图片，如果有则删除
                if (!empty($list_setting['default_user_portrait']) && !empty($_POST['default_user_portrait'])) {
                    @unlink(BASE_UPLOAD_PATH . DS . ATTACH_COMMON . DS . $list_setting['default_user_portrait']);
                    @unlink(BASE_UPLOAD_PATH . DS . ATTACH_COMMON . DS . str_ireplace(',', '_small.', $list_setting['default_user_portrait']));
                }
                $this->log(L('nc_edit,default_thumb'), 1);
                showMessage(L('nc_common_save_succ'));
            } else {
                $this->log(L('nc_edit,default_thumb'), 0);
                showMessage(L('nc_common_save_fail'));
            }
        }

        $list_setting = $model_setting->getListSetting();

        //模板输出
        Tpl::output('list_setting', $list_setting);

        //输出子菜单
        Tpl::output('top_link', $this->sublink($this->links, 'default_thumb'));
        //
        Tpl::setDirquna('system');
        Tpl::showpage('upload.thumb');
    }
}
