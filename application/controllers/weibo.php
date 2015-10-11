<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Weibo extends CI_Controller {
	function __construct() {
		parent::__construct();
		$this->load->model('mweibo','m');
	}
	
	function add() {
		$this->load->model('muser','user');
		if (!$this->user->check()) noRights();
		$data=$this->input->post(['address','content','images']);
		if (!$data) errInput();
		$data['images']=gzcompress($data['images']);
		$data['authorId']=UID;
		$data['time']=time();
		$flag=$this->db->insert('weibo',$data);
		$flag?ajax():busy();
	}
	
	function del() {
		$this->load->model('muser','user');
		if (!$this->user->check()) noRights();
		$id=$this->input->post('id');
		$uid=$this->db->find('weibo',$id,'id','uid');
		if (!empty($uid)&&$uid['uid']==UID){
			$this->db->delete('weibo',['id'=>$id])?ajax():busy();//做了外键约束，只删微博就够了
		}else attack();
	}
	
	function comment() {
		$this->load->model('muser','user');
		if (!$this->user->check()) noRights();
		$data=$this->input->post(['fid','wid','content']);
		if (!$data) errInput();
		$data['uid']=UID;
		$data['time']=time();
		$flag=$this->db->insert('wcomment',$data);
		$flag?ajax():busy();
	}
	
	function getList() {
		$type=$this->input->post('type');
		if (!$type) errInput();
		$input=['type'=>$type,'page'=>$this->input->post('page',FALSE,0),'count'=>$this->input->post('count',FALSE,20)];
		$this->load->model('mweibo','m');
		ajax(0,'',$this->m->getList($input));
	}
	
	function getItem() {
		$id=$this->input->post('id');
		$this->load->model('mweibo','m');
		$data=$this->m->getItem($id);
		if ($data==FALSE) ajax(3001,'没有数据');
		else ajax(0,'',$data);
	}
}