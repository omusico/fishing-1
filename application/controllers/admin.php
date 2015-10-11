<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class admin extends CI_Controller {
	function __construct() {
		parent::__construct();
		if (!isset($_COOKIE['no_access']))
			show_404();
		$this->load->library('session');
		if (!$this->session->userdata('admin')){
			show_404();
		}
	}
	
	function index() {
		
	}
	
	function qiniuToken(){
		$this->load->library('qiniu');
		ajax(0,$this->qiniu->uploadToken());
	}
	
	function newVersion() {
		$this->db->insert('version',$this->input->post(NULL,true))?ajax():busy();
	}
}