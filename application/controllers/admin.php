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

	function view($value=''){
		preg_match("/^([a-z])+$/i", $value) OR errInput();
		$this->load->view($value);
	}
	
	function temp($id=0) {
		$data=$this->db->find('place', $id);
		if (!$data) die('no data');
		$show=['picture'=>gzuncompress($data['picture']),'id'=>$id];
		$this->load->view('test',$show);
	}
	
	function resetData() {
		$res=$this->db->select('id,preview')->get('place')->result_array();
		foreach ($res as $value) {
			$this->db->where('id',$value['id'])->update('place',['picture'=>gzcompress("[\"$value[preview]\"]")]);
		}
		echo 'ok';
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
		if ($id==0||!is_numeric($id)) attack();
		$data=$this->db->find('place', $id);
		$data OR die('钓点不存在');
		$data['picture']=json_decode(gzuncompress($data['picture']),TRUE);
		ajax(0,'',$data);
	}
	
	function placeCheck() {
		$data=$this->input->post(['id','state']) OR errInput();
		$res=$this->db->find('place', $data['id'],'id','uid,name');
		$res OR attack();
		$this->load->model('notify');
		if ($data['state']==1){
			$this->notify->add(Notify::PASS,$res,
					$data['id'],$res['uid']);
			$this->notify->add(Notify::ADD,$res,
					$data['id']);
		}else {
			$res['info']=$this->input->post('info',FALSE);
			$this->notify->add(Notify::FAIL,$res,
					$data['id'],$res['uid']);
		}
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
	
	function log($date='') {
		if ($date=='') {
			$time=time()-86400;
			$date=date('Y-m-d',$time);
		}else if (!preg_match("/^([0-9-])+$/i", $date)) errInput();
		$date=APPPATH."logs/$date.json";
		if (!file_exists($date)) die('此日志文件不存在！');
		$data=json_decode(file_get_contents($date),TRUE);
		foreach ($data['url'] as $key=>$value) {
			echo $key.'模块共访问了'.$value['total'].'次，具体访问情况如下：<br />';
			unset($value['total']);
			foreach ($value as $key2 => $value2) {
				echo "$key2 接口访问了$value2 次<br />";
			}
			echo '<br />';
		}
		echo '<br />';
		echo '此日共计有'.count($data['user']).'人使用。<br />';
		$res=$this->db->select('id,name')->where_in('id',array_keys($data['user']))->get('user')->result_array();
		$user=[];
		foreach ($res as $item){
			$user[$item['id']]=$item['name'];
		}
		foreach ($data['user'] as $key => $value) {
			echo "ID是$key 的用户“".$user[(string)$key]."”访问过$value 次接口。<br />";
		}
	}
}