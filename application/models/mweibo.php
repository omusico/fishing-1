<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Mweibo extends CI_Model {
	function getItem($id) {
		$data=$this->db->find('weibo',$id,'id','*,(SELECT avatar FROM user WHERE id=weibo.authorId) authorAvatar,(SELECT name FROM user WHERE id=weibo.authorId) authorName');
		if (!$data)
			return FALSE;
		$data['images']=json_decode(gzuncompress($data['images']),TRUE);
		$comment=$this->db->field('*,(SELECT avatar FROM user WHERE id=weibo.authorId) authorAvatar,(SELECT name FROM user WHERE id=weibo.authorId) authorName')
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
		$this->db->where('authorId',$uid)->limit($limit['page']*$limit['size'],$limit['size'])->order_by('id','desc');
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