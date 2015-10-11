<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Muser extends CI_Model {
	function checkTel($tel) {
		if (!$tel||!is_numeric($tel)) return FALSE;
		$res=$this->db->find('user',$tel,'tel');
		if (empty($res))
			return TRUE;
		else return FALSE;
	}
	
	function check() {
		$data=getToken();
		$res=$this->db->find('user', $data['id'],'id','token');
		if (empty($res)||$res['token']!=$data['token'])
			return FALSE;
		else{
			define('UID', $data['id']);
			return TRUE;
		}
	}
	
	function login($input) {
		$result =$this->db->find('user', $input['tel'],'tel');
		if (empty($result)) ajax(1001,'此用户不存在！');
		if ($result['password']!==md5(md5($input['password']).'fish')) ajax(1003,'密码错误！');
		$result['token'] = md5(uniqid().rand());
		if ($result['rongToken']==''){
			$this->load->library('rc');
			$token=$this->rc->RCgetToken($result);
			if ($token['status'])
				$result['rongToken']=$token['code'];
			else ajax(2,'融云平台出错！'.$token['code']);
		}
		$weibo=$this->db->query("SELECT * FROM weibo WHERE uid=? ORDER BY id desc LIMIT 3")->result_array();
		$result['seed']=array();
		foreach ($weibo as $value) {
			$value['images']=json_decode($value['images'],TRUE);
			$value['authorAvatar']=$result['avatar'];
			$value['authorName']=$result['name'];
			$value['praiseStatus']=$this->db->query("SELECT * FROM praise WHERE uid=$result[id] AND wid=$value[id]")->num_rows();
			$result['seed'][]=$value;
		}
		$this->db->where('id',$result['id'])->update('user',['token'=>$result['token'],'type'=>0,'rongToken'=>$result['rongToken']]);
		unset($result['password']);
		return $result;
	}
}