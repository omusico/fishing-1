<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Mweibo extends CI_Model {
	function getItem($id) {
		$data=$this->db->find('weibo',$id,'id','*,(SELECT avatar FROM user WHERE id=weibo.authorId) authorAvatar,(SELECT name FROM user WHERE id=weibo.authorId) authorName');
		if (!$data)
			return FALSE;
		$data['images']=json_decode(gzuncompress($data['images']),TRUE);
		$comment=$this->db->field('*,(SELECT avatar FROM user WHERE id=weibo.uid) authorAvatar,(SELECT name FROM user WHERE id=weibo.authorId) uid')
			->where('wid',$id)->get('wcomment');
		$link=array();$res=array();//链表，link存每个id的地址，res是结果，指针都指向comment的数据。
		foreach ($comment as $key=>$value) {
			$comment[$key]['child']=array();
			$link[$comment[$key]['id']]=&$comment[$key];
			if ($comment[$key]['fid']==0){
				$res[]=&$comment[$key];
			}else {
				if (!isset($link[$comment[$key]['fid']]))
					$this->db->delete('wcomment',['id'=>$comment[$key]['fid']]);
				$link[$comment[$key]['fid']]['child'][]=&$comment[$key];
			}
		}
		$data['comment']=$res;
		return $data;
	}
	
	function getList($uid,$limit) {
		$this->db->limit($limit['page']*$limit['count'],$limit['count']);
		switch ($limit['type']){
			case 0:$this->db->order_by('visitCount desc,id desc');
				break;
			case 1:
				$this->load->model('muser','user');
				if (!$this->user->check()) noRights();
				$this->db->where("authorId IN (SELECT toId FROM attention WHERE fromId=?)",UID,FALSE)->order_by('id desc');
				break
		}
		$data=$this->db->select('*,(SELECT avatar FROM user WHERE id=weibo.authorId) authorAvatar,(SELECT name FROM user WHERE id=weibo.authorId) authorName')->get('weibo');
		return $this->_dealData($data);
	}
	
	function _dealData(&$data) {
		foreach ($data as $key => $value) {
			$data['key']['images']=json_decode(gzuncompress($data['key']['images']),TRUE);
		};
		return $data;
	}
}