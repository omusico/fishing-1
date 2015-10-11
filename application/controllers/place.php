<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Place extends CI_Controller {
	function add() {
		$this->load->model('muser','user');
		if (!$this->user->check()) noRights();
		$data=$this->db->create('place');
		if (isset($data['state']))//有审核状态，直接干掉
			attack();
		$data['picture']=gzcompress($data['picture']);
		$data['uid']=UID;
		$data['time']=time();
		$this->db->insert('place',$data)?ajax():busy();
	}
	
	function getPlace() {
		$time=$this->input->post('time');
		$data=$this->db->query("SELECT * FROM place WHERE time>? AND state=1",$time)->result_array();
		ajax(0,'ok',$data);
	}
	
	function getItem() {
		$id=$this->input->post('id');
		$data=$this->db->find('place', $id);
		if (empty($data)) ajax(2001,'没有数据');
		$data['picture']=json_decode($data['picture'],TRUE);
		$this->load->model('muser','user');
		if ($this->user->check())
			$data['collectStatus']=$this->db->where(['pid'=>$id,'uid'=>UID])->get('collection')->num_rows();
		ajax(0,'',$data);
	}
	
	function score() {
		$this->load->model('muser','user');
		if (!$this->user->check()) noRights();
		$data=$this->input->post(['score','content','images','pid']);
		if (!$data) errInput();
		if (!is_numeric($data['score'])||$data['score']<0||$data['score']>10) attack();
		$data['images']=gzcompress($data['images']);
		$data['author']=UID;
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
		$flag?ajax():busy();
	}
	
	function scoreDetail() {
		$id=$this->input->post('id');
		if (!$id) errInput();
		$data=$this->m->scoreDetail($id);
		$data?ajax(0,'',$data):ajax(2002,'评价不存在');
	}
	
	function scoreList() {
		$id=$this->input->post('id');
		if (!$id) errInput();
		$input=['id'=>$id,'page'=>$this->input->post('page',FALSE,0),'count'=>$this->input->post('count',FALSE,20)];
		$this->load->model('mplace','m');
		$data=$this->m->scoreList($input);
		$data?ajax(0,'',$data):ajax(2001,'钓点不存在');
	}
}