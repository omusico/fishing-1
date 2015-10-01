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
}