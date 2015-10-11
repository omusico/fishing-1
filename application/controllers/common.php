<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Common extends CI_Controller {
	function index(){
		echo '呵呵';
	}
	
	function build() {
		$this->load->dbforge();
		$this->dbforge->column_cache();
	}
	
	function qiniuToken(){
		$this->load->model('muser');
		if (!$this->muser->check()) noRights();
		$this->load->library('qiniu');
		ajax(0,'',array("token"=>$this->qiniu->uploadToken()));
	}
	
	function version() {
		$data=$this->db->query("SELECT *,unix_timestamp(time) time FROM version ORDER BY id desc LIMIT 1")->row_array();
		ajax(0,'',$data);
	}
	
	function whereisfish() {
		$this->load->library('session');
		if ($this->session->userdata('admin'))
			header('Location:/admin/');
		else{
			$data=$this->input->post(['user','pwd']);
			if ($data){
				if ($data['user']=='small'&&md5($data['pwd'])){
					$this->load->library('session');
					$this->session->set_userdata('admin','ok');
					ajax(0,'/admin/');
				}else busy();
			}else $this->load->view('login');
		}
	}
}