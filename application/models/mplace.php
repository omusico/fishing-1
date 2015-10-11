<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Mplace extends CI_Model {
	function scoreDetail($id) {
		$data=$this->db->find('score',$id,'id');
		if (!$data)
			return FALSE;
		$data['images']=json_decode(gzuncompress($data['images']),TRUE);
		$comment=$this->db->field('*,(SELECT avatar FROM user WHERE id=scomment.uid) authorAvatar,(SELECT name FROM user WHERE id=scomment.uid) authorName')
			->where('pid',$id)->get('scomment');
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
	
	function scoreList($input) {
		$this->db->limit($input['count']*$input['page'],$input['count'])->order_by('id','desc');
		$data=$this->db->where('pid',$input['id'])->get('score')->result_array();
		foreach ($data as $key => $value) {
			$data[$key]['images']=json_decode(gzuncompress($data[$key]['images']),TRUE);
		}
		return $data;
	}
}