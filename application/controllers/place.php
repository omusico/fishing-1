<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Place extends CI_Controller {
	function add() {
		if (!$this->m->check()) noRights();
		$data=$this->db->create('place');
		if (isset($data['state']))//有审核状态，直接干掉
			busy();
		$data['uid']=UID;
		$data['time']=time();
		$this->db->insert('place',$data)?ajax():busy();
	}
	
	function getPlace() {
		$time=$this->input->post('time');
		$data=$this->db->query("SELECT * FROM place WHERE time>?",$time)->result_array();
		ajax(200,'ok',$data);
	}
	
	function score() {
		$this->load->model('muser','user');
		if (!$this->user->check()) noRights();
		$data=$this->input->post(['score','content','images']);
		if (!$data) busy();
		$data['author']=UID;
		$data['time']=time();
		$this->db->insert('score',$data);
		ajax();
	}
}