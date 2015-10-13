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
	
	function test() {
		$this->db->where('id <',6)->update('user',['addrTime'=>time()]);
// 		$data=array('picture'=>gzcompress('["http://t11.baidu.com/it/u=1889789971,2360758735&fm=58"]'),
// 				'preview'=>'http://t11.baidu.com/it/u=1889789971,2360758735&fm=58',
// 				'time'=>time(),'address'=>'重庆磁器口','fishType'=>'逗鱼','serviceType'=>'1,2','content'=>'test','tel'=>'123','state'=>1);
// 		$res=array();
// 		for ($i = 0; $i < 20; $i++) {
// 			$data['lat']=28+rand()*3;$data['lng']=108+rand()*3;
// 			$data['name']="No.$i";
// 			$data['costType']=rand(0,1);
// 			$data['cost']=$data['costType']?rand(100,500):0;
// 			$res[]=$data;
// 		}
// 		$this->db->insert_batch('place',$res);
	}
}