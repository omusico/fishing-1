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
		$this->load->view('index');
	}
	
	function placeList() {
		$data=$this->db->select('id,(SELECT name FROM user WHERE id=score.uid) user,name,cost,briefAddr,time')
			->where('state',0)->get('place')->result_array();
		ajax(0,'',$data);
	}
	
	function placeItem($id) {
		if ($state=$this->input->post('state')){
			$this->db->where('id',$id)->update('place',['state'=>$state]);
			ajax();
		}else {
			$data=$this->db->find('place', $id);
			$data OR die('钓点不存在');
			$data['picture']=json_decode(gzuncompress($data['picture']),TRUE);
			$this->load->view('placeItem',$data);
		}
	}
	
	function qiniuToken(){
		$this->load->library('qiniu');
		ajax(0,$this->qiniu->uploadToken());
	}
	
	function newVersion() {
		if ($data=$this->input->post(NULL,true))
			$this->db->insert('version',$data)?ajax():busy();
		else $this->load->view('version');
	}
}