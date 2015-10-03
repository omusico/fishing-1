<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Muser extends CI_Model {
	function checkTel($tel) {
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
		if (empty($result)) ajax(202,'此用户不存在！');
		if ($result['password']!==md5(md5($info['password']).'fish')) ajax(201,'密码错误！');
		$result['token'] = md5(uniqid().rand());
		$weibo=$this->db->query("SELECT * FROM weibo WHERE uid=? ORDER BY id desc LIMIT 3");
		if ($result['rongToken']==''){
			$this->load->library('rc');
			$token=$this->rc->RCgetToken($result);
			if ($token['status'])
				$result['rongToken']=$token['token'];
			else ajax(203,'融云平台出错！');
		}
		$this->db->where('id',$result['id'])->update('user',['token'=>$result['token'],'type'=>0,'rongToken'=>$result['rongToken']]);
		unset($result['password']);
	}
}