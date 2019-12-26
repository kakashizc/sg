<?php
/**
 * 管理
 */
defined('InShopBN') or exit('Access Invalid!');
class LevelModel extends Model{
    public function __construct() {
        parent::__construct('level');
    }
	
	 public function getLevelInfo($condition, $field = '*', $master = false) {
		return $this->table('level')->field($field)->where($condition)->master($master)->find();
    }
	
	public function getLevelById($member_id, $fields = '*') {
        $member_info = $this->getLevelInfo(array('id'=>$member_id),'*',true);
        return $member_info;
    }

}