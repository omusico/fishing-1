<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Bbs extends CI_Controller {
	function add() {
		$this->load->model('muser');
		$this->muser->check() OR noRights();
		$data=$this->input->post(['title','content','pic'],TRUE) OR errInput();
		$data['time']=time();
		$data['uid']=UID;
		$data['pic']=gzcompress($data['pic']);
		$this->db->insert('bbs',$data)?ajax():busy();
	}
	
	function del(){
		$this->load->model('muser');
		$this->muser->check() OR noRights();
		$id=(int)$this->input->post('id');
		$uid=$this->db->find('bbs', $id,'id','uid') OR ajax(4001,'帖子不存在');
		if ($uid['uid']!=UID) attack();
		$this->db->delete('bbs',['id'=>$id])?ajax():busy();//有外键，只删除bbs就可以了
	}
	
	function getList() {
		$page=(int)$this->input->post('page',FALSE,0);
		$count=(int)$this->input->post('count',FALSE,10);
		$select='id,(SELECT name FROM user WHERE id=uid) authorName,title,time';
		if ($page==0) $sql="(SELECT $select FROM bbs ORDER BY rank desc LIMIT 3) UNION (SELECT $select FROM bbs ORDER BY time desc LIMIT ".($count-3).')';
		else $sql="SELECT $select FROM bbs ORDER BY time desc LIMIT ".($page*$count).",$count";
		ajax(0,'',$this->db->query($sql)->result_array());
	}
	
	function item() {
		$id=(int)$this->input->post('id');
		$this->db->where('id',$id)->step('visit','bbs') OR ajax(4001,'帖子不存在');
		$data=$this->db->find('bbs', $id,'id','id,title,content,pic,time,(SELECT avatar FROM user WHERE id=uid) authorAvatar,(SELECT name FROM user WHERE id=uid) authorName');
		$data['pic']=json_decode(gzuncompress($data['pic']),TRUE);
		$data['pic']=$data['pic']?:array();
		ajax(0,'',$data);
	}
	
	function comment() {
		$this->load->model('muser');
		$this->muser->check() OR noRights();
		$data=$this->input->post(['bid','content'],TRUE) OR errInput();
		$bbs=['time'=>time(),'reply'=>'reply+1'];
		$this->db->where('id',$data['bid'])->set($bbs,'',FALSE)->update('bbs') OR ajax(4001,'帖子不存在');
		$data['time']=$bbs['time'];
		$data['uid']=UID;
		$this->db->insert('bcomment',$data)?ajax():busy();
	}
	
	function commentList() {
		$id=(int)$this->input->post('id');
		$page=(int)$this->input->post('page',FALSE,0);
		$count=(int)$this->input->post('count',FALSE,10);
		$data=$this->db->select('id,content,time,(SELECT avatar FROM user WHERE id=uid) authorAvatar,(SELECT name FROM user WHERE id=uid) authorName')
			->where('bid',$id)->limit($count,$count*$page)->order_by('id','desc')
			->get('bbs')->result_array();
		ajax(0,'',$data);
	}
}