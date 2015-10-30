<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Weibo extends CI_Controller {
	function __construct() {
		parent::__construct();
		$this->load->model('mweibo','m');
	}
	
	function add() {
		$this->load->model('muser','user');
		if (!$this->user->check()) noRights();
		$data=$this->input->post(['address','content','images','lat','lng']);
		$data OR errInput();
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
		$uid=$this->db->find('weibo',$id,'id','authorId');
		if (!empty($uid)&&$uid['authorId']==UID){
			$this->db->delete('weibo',['id'=>$id])?ajax():busy();//做了外键约束，只删微博就够了
		}else attack();
	}
	
	function comment() {
		$this->load->model('muser','user');
		if (!$this->user->check()) noRights();
		$data=$this->input->post(['fid','wid','content']);
		if (!$data) errInput();
		$this->m->comment($data)?ajax():busy();		
	}
	
	function praise() {
		$this->load->model('muser','user');
		if (!$this->user->check()) noRights();
		$id=$this->input->post('id');
		if (!$id) errInput();
		$this->m->praise($id)?ajax():busy();
	}
	
	function unPraise() {
		$this->load->model('muser','user');
		if (!$this->user->check()) noRights();
		$id=$this->input->post('id');
		if (!$id) errInput();
		$this->m->unPraise($id)?ajax():busy();
	}
	
	function getListGround() {
		$input=['type'=>0,'page'=>$this->input->post('page',FALSE,0),'count'=>$this->input->post('count',FALSE,20)];
		ajax(0,'',$this->m->getList($input));
	}
	
	function getListFriend() {
		$this->load->model('muser','user');
		if (!$this->user->check()) noRights();
		$input=['type'=>1,'page'=>$this->input->post('page',FALSE,0),'count'=>$this->input->post('count',FALSE,20)];
		ajax(0,'',$this->m->getList($input));
	}
	
	function getListNear() {
		$input=$this->input->post(['lng','lat']);
		if (!$input) errInput();
		$input=array_merge($input,['type'=>2,'page'=>$this->input->post('page',FALSE,0),'count'=>$this->input->post('count',FALSE,20)]);
		ajax(0,'',$this->m->getList($input));
	}
	
	function getList() {
		$this->load->model('muser','user');
		$input=['type'=>3,'page'=>$this->input->post('page',FALSE,0),'count'=>$this->input->post('count',FALSE,20),'id'=>$this->input->post('id',FALSE,0)];
		ajax(0,'',$this->m->getList($input));
	}
	
	function getItem() {
		$id=$this->input->post('id');
		$id OR errInput();
		$data=$this->m->getItem($id);
		if ($data==FALSE) ajax(3001,'没有数据');
		else ajax(0,'',$data);
	}
}