<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Yue extends CI_Controller {
	function add() {
		$this->load->model('muser');
		$this->muser->check() OR noRights();
		$data=$this->input->post(['title','content','address','acTime'],TRUE) OR errInput();
		$data['time']=time();
		$data['uid']=UID;
		$this->db->insert('yue',$data)?ajax():busy();
	}
	
	function del(){
		$this->load->model('muser');
		$this->muser->check() OR noRights();
		$id=(int)$this->input->post('id') OR errInput();
		$uid=$this->db->find('yue', $id,'id','uid') OR ajax(5001,'此记录不存在');
		if ($uid['uid']!=UID) attack();
		$this->db->delete('yue_people',['yid'=>$id]);
		$this->db->delete('bbs',['id'=>$id])?ajax():busy();
	}
	
	function getList() {
		$page=(int)$this->input->post('page',FALSE,0);
		$count=(int)$this->input->post('count',FALSE,10);
		$res=$this->db->select('id,uid,(SELECT name FROM user WHERE user.id=uid) authorName,(SELECT avatar FROM user WHERE user.id=uid) authorAvatar,title,time')
			->limit($count,$count*$page)
			->get('yue')->result_array();
		ajax(0,'',$res);
	}
	
	function item() {
		$id=(int)$this->input->post('id');
		$this->db->where('id',$id)->step('visit','bbs') OR ajax(5001,'此记录不存在');
		$data=$this->db->find('yue', $id,'id','*,(SELECT avatar FROM user WHERE user.id=uid) authorAvatar,(SELECT name FROM user WHERE user.id=uid) authorName');
		$data OR ajax(5001,'此记录不存在');
		ajax(0,'',$data);
	}
	
	function enroll() {
		$this->load->model('muser');
		$this->muser->check() OR noRights();
		$id=(int)$this->input->post('id');
		$this->db->insert('yue_people',['uid'=>UID,'yid'=>$id])?ajax():busy();
	}
	
	function enrollList() {
		$this->load->model('muser');
		$this->muser->check() OR noRights();
		$id=(int)$this->input->post('id') OR errInput();
// 		$uid=$this->db->find('yue', $id,'id','uid');
// 		$uid OR ajax(5001,'此记录不存在');
// 		$uid==UID OR noRights();
		$res=$this->db->query("SELECT id,avatar,name FROM (SELECT uid FROM yue_people WHERE yid=?) people JOIN user ON uid=id",$id)->result_array();
		ajax(0,'',$res);
	}
	
	function getMyList() {
		$this->load->model('muser');
		$this->muser->check() OR noRights();
		$res=$this->db->where("id in (SELECT yid FROM yue_people WHERE uid=".UID.")",null,FALSE)
			->select("id,uid,(SELECT name FROM user WHERE user.id=uid) authorName,(SELECT avatar FROM user WHERE user.id=uid) authorAvatar,title,time")
			->get('yue')->result_array();
		ajax(0,'',$res);
	}
}