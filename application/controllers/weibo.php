<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Weibo extends CI_Controller {
	function __construct() {
		parent::__construct();
		$this->load->model('mweibo','m');
	}
	
	function add() {
		$this->load->model('muser','user');
		if (!$this->user->check()) noRights();
		$data=$this->input->post(['address','content','images']);
		if (!$data) busy();
		$data['author']=UID;
		$data['time']=time();
		$this->db->insert('weibo',$data);
		ajax();
	}
	
	function test() {
	}
}