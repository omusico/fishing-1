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
		$input=['type'=>$this->input->post('type',NULL,0),
				'page'=>$this->input->post('page',FALSE,0),
				'count'=>$this->input->post('count',FALSE,10)];
		$data=$this->db->select('id,name,briefAddr,time')
			->where('state',$input['type'])->limit($input['count'],$input['count']*$input['page'])->get('place')->result_array();
		ajax(0,'',$data);
	}
	
	function placeItem($id=0) {
// 		if ($id==0||!is_numeric($id)) return $this->load->view('placeItem');
		$data=$this->db->find('place', $id);
		$data OR die('钓点不存在');
		$data['picture']=json_decode(gzuncompress($data['picture']),TRUE);
		ajax(0,'',$data);
	}
	
	function placeCheck() {
		$data=$this->input->post(['id','state']) OR errInput();
		$this->db->query("UPDATE place SET state=? WHERE id=?",[$data['state'],$data['id']])?ajax():busy();
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