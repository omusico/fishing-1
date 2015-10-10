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
}