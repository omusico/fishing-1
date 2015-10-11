<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Mweibo extends CI_Model {
	function getItem($id) {
		$data=$this->db->find('weibo',$id,'id','*,(SELECT avatar FROM user WHERE id=weibo.authorId) authorAvatar,(SELECT name FROM user WHERE id=weibo.authorId) authorName');
		if (!$data)
			return FALSE;
		$this->db->query("UPDATE weibo SET visitCount=visitCount+1 WHERE id=$data[id]");
		$data['visitCount']++;
		$comment=$this->db->field('*,(SELECT avatar FROM user WHERE id=weibo.uid) authorAvatar,(SELECT name FROM user WHERE id=weibo.authorId) uid')
			->where('wid',$id)->get('wcomment')->result_array();
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
		return $this->_dealData($data);
	}
	
	function getList($limit) {
		$this->db->limit($limit['count'],$limit['page']*$limit['count']);
		switch ($limit['type']){
			case 0:$this->db->order_by('visitCount desc,id desc');
				break;
			case 1:
				$this->db->where("authorId IN (SELECT toId FROM attention WHERE fromId=?)",UID,FALSE)->order_by('id desc');
				break;
			case 2:
				$this->db->where("time>?",time()-1296000,FALSE)->order_by("sqrt(lat-$input[lat])+sqrt((lng-$input[lng])*cos((lat+$input[lat])/2))",'desc');
				break;
			case 3:$this->db->where('authorId',UID);
				break;
			default:errInput();
		}
		$data=$this->db->select('*,(SELECT avatar FROM user WHERE id=weibo.authorId) authorAvatar,(SELECT name FROM user WHERE id=weibo.authorId) authorName')->get('weibo')->result_array();
		array_walk($data, [$this,'_dealData']);
		return $this->_dealData($data);
	}
	
	function _dealData(&$data) {
		$data['images']=json_decode(gzuncompress($data['images']),TRUE);
		$data['praiseCount']=$this->db->where('wid',$data['id'])->count_all_results('praise');
		$data['commentCount']=$this->db->where('wid',$data['id'])->count_all_results('wcomment');
		$this->load->model('muser','user');
		if (defined('UID')||$this->user->check())
			$data['praiseStatus']=$this->db->where(['wid'=>$data['id'],'uid'=>UID])->get('praise')->num_rows();
		$data['praiseMember']=$this->db->query("SELECT id,name,sign,avatar FROM user WHERE id IN (SELECT uid FROM (SELECT uid FROM praise WHERE wid=? LIMIT 6 ORDER BY id desc) as t)",$data['id'])->result_array();
		return $data;
	}
}