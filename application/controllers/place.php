<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Place extends CI_Controller {
	function add() {
		$this->load->model('muser','user');
		if (!$this->user->check()){
			$this->load->library('session');
			$this->session->userdata('admin') OR noRights();//no login,not admin
			define('UID', 1);
		}
		$data=$this->db->create('place');
		if (!isset($data['id']))
			$data['id']=0;
		if (isset($data['state'])||!is_numeric($data['id']))//有审核状态，直接干掉
			attack();
		if (isset($data['picture'])&&is_array(json_decode($data['picture'],TRUE)))
			$data['picture']=gzcompress($data['picture']);
		$data['uid']=UID;
		if ($data['id']!=0){
			$uid=$this->db->find('', $data['id'],'id','uid');
			$uid OR ajax(2001,'钓点不存在');
			if ($uid['uid']!=UID&&UID!=1) attack();//not author,not admin
			$data['state']=0;
			$this->db->where('id',$data['id'])->update('place',$data)===FALSE?busy():ajax();
		}
		unset($data['id']);
		$this->db->insert('place',$data)?ajax():busy();
	}
	
	function del() {
		$id=$this->input->post('id');
		$this->load->model('muser','user');
		if (!$this->user->check()){
			$this->load->library('session');
			$this->session->userdata('admin') OR noRights();//no login,not admin
			define('UID', 1);
		}
		$uid=$this->db->find('place', $id,'id','uid');
		$uid OR ajax(2001,'钓点不存在');
		if ($uid['uid']!=UID&&UID!=1) attack();//not author,not admin
		$this->db->delete('place',['id'=>$id])?ajax():busy();
	}
	
	function getPlace() {
		$time=$this->input->post('time',FALSE,0);
		$data=$this->db->query("SELECT id,name,preview,briefAddr,score,cost,costType,fishType,poolType,serviceType,lat,lng,state FROM place WHERE unix_timestamp(time)>?",$time)->result_array();
		ajax(0,'ok',$data);
	}
	
	function getItem() {
		$id=$this->input->post('id');
		$id OR errInput();
		$data=$this->db->find('place', $id,'id','*,(SELECT avatar FROM user WHERE id=place.uid) authorAvatar,(SELECT name FROM user WHERE id=place.uid) authorName');
		if (empty($data)) ajax(2001,'钓点不存在');
		$data['picture']=json_decode(gzuncompress($data['picture']),TRUE);
		$data['evaluateCount']=$this->db->where('pid',$id)->count_all_results('score');
		$this->load->model('muser','user');
		if ($this->user->check())
			$data['collectStatus']=$this->db->where(['pid'=>$id,'uid'=>UID])->get('collection')->num_rows()==1?TRUE:FALSE;
		ajax(0,'',$data);
	}
	
	function myCollect() {
		$this->load->model('muser','user');
		$this->user->check() OR noRights();
		$data=$this->db->query("SELECT id,name,preview,briefAddr,score,cost,costType,fishType,poolType,serviceType,lat,lng,state FROM place WHERE id in (SELECT pid FROM collection WHERE uid=?)",UID)->result_array();
		ajax(0,'ok',$data);
	}
	
	function myPlace() {
		$this->load->model('muser','user');
		$this->user->check() OR noRights();
		$data=$this->db->query("SELECT id,name,preview,briefAddr,score,cost,costType,fishType,poolType,serviceType,lat,lng,state FROM place WHERE uid=?",UID)->result_array();
		ajax(0,'ok',$data);
	}
	
	function collect() {
		$this->load->model('muser','user');
		$this->user->check() OR noRights();
		$id=$this->input->post('id') OR errInput();
		$this->db->insert('collection',['pid'=>$id,'uid'=>UID]);
		ajax();
	}
	
	function unCollect() {
		$this->load->model('muser','user');
		$this->user->check() OR noRights();
		$id=$this->input->post('id') OR errInput();
		$this->db->where(['pid'=>$id,'uid'=>UID])->delete('collection');
		ajax();
	}
	
	function score() {
		$this->load->model('muser','user');
		if (!$this->user->check()) noRights();
		$data=$this->input->post(['score','content','images','pid']);
		if (!$data) errInput();
		if (!is_numeric($data['score'])||$data['score']<0||$data['score']>10) attack();
		is_array(json_decode($data['images'],TRUE)) OR errInput();
		$data['images']=gzcompress($data['images']);
		$data['uid']=UID;
		$data['time']=time();
		$flag=$this->db->insert('score',$data);
		if (!$flag) busy();
		$score=$this->db->query("SELECT count(*) num,sum(`score`) total FROM score WHERE pid=?",$data['pid'])->row();
		$newS=$score->total*10/$score->num;//添加评价了，num不可能为0
		$this->db->where('id',$data['pid'])->update('place',['score'=>round($newS)/10])?ajax():busy();
	}
	
	function comment() {
		$this->load->model('muser','user');
		if (!$this->user->check()) noRights();
		$data=$this->input->post(['fid','sid','content']);
		if (!$data) errInput();
		$data['uid']=UID;
		$data['time']=time();
		$flag=$this->db->insert('scomment',$data);
		$this->db->where('id',$data['sid'])->step('commentCount','score');
		$flag?ajax():busy();
	}
	
	function scoreDetail() {
		$id=$this->input->post('id');
		if (!$id) errInput();
		$this->load->model('mplace','m');
		$data=$this->m->scoreDetail($id);
		$data?ajax(0,'',$data):ajax(2002,'评价不存在');
	}
	
	function scoreList() {
		$id=$this->input->post('id');
		if (!$id) errInput();
		$input=['id'=>$id,'page'=>$this->input->post('page',FALSE,0),'count'=>$this->input->post('count',FALSE,20)];
		$this->load->model('mplace','m');
		$data=$this->m->scoreList($input);
		ajax(0,'',$data);
	}
	
	function myScoreList() {
		$this->load->model('muser','user');
		if (!$this->user->check()) noRights();
		$input=['page'=>$this->input->post('page',FALSE,0),'count'=>$this->input->post('count',FALSE,20)];
		$this->load->model('mplace','m');
		$data=$this->m->myScoreList($input);
		ajax(0,'',$data);
	}
}