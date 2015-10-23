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
				if ($data['user']=='small'&&md5($data['pwd'])=='1a833da63a6b7e20098dae06d06602e1'){
					$this->load->library('session');
					$this->session->set_userdata('admin','ok');
					ajax(0,'/admin/');
				}else busy();
			}else $this->load->view('login');
		}
	}
	
	function reportPlace(){
		$this->load->model('muser');
		$this->muser->check() OR noRights();
		$data=$this->input->post(['content','id']) OR errInput();
		$data['uid']=UID;
		$data['type']=0;
		$this->db->insert('report',$data)?ajax():busy();
	}
	
	function reportWeibo(){
		$this->load->model('muser');
		$this->muser->check() OR noRights();
		$data=$this->input->post(['content','id']) OR errInput();
		$data['uid']=UID;
		$data['type']=1;
		$this->db->insert('report',$data)?ajax():busy();
	}
	
	function feedback(){
		$this->load->model('muser');
		$this->muser->check() OR noRights();
		$data=$this->input->post('content',TRUE) OR errInput();
		$this->db->insert('feedback',['content'=>$data,'uid'=>UID])?ajax():busy();
	}

	function test() {
		// $data=$this->db->select('id,picture')->get('place')->result_array();
		// $update=['picture'=>gzcompress('["http://t11.baidu.com/it/u=1889789971,2360758735&fm=58"]')];
		// foreach ($data as $value) {
		// 	$t=gzuncompress($value['picture']);
		// 	if (!$t){
		// 		$this->db->where('id',$value['id'])->update('place',$update);
		// 	}
		// }
	}
}