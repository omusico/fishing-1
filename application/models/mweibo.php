<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Mweibo extends CI_Model {
	function getItem($id) {
		$data=$this->db->find('weibo',$id,'id','*,(SELECT avatar FROM user WHERE id=weibo.authorId) authorAvatar,(SELECT name FROM user WHERE id=weibo.authorId) authorName');
		if (!$data)
			return FALSE;
		$this->db->query("UPDATE weibo SET visitCount=visitCount+1 WHERE id=$data[id]");
		$data['visitCount']++;
		$comment=$this->db->select('*,(SELECT avatar FROM user WHERE id=wcomment.uid) authorAvatar,(SELECT name FROM user WHERE id=wcomment.uid) authorName')
			->where('wid',$data['id'])->get('wcomment')->result_array();//先发表的放前面
		$link=array();$res=array();$del=false;//链表，link存每个id的地址，res是结果，指针都指向comment的数据。
		foreach ($comment as $key=>$value) {
			$comment[$key]['child']=array();
			$link[$comment[$key]['id']]=&$comment[$key];
			if ($comment[$key]['fid']==0){
				$res[]=&$comment[$key];
			}else {
				if (!isset($link[$comment[$key]['fid']])){
					$del=TRUE;
					$this->db->delete('wcomment',['id'=>$comment[$key]['id']]);
				}
				$link[$comment[$key]['fid']]['child'][]=&$comment[$key];
			}
		}
		$data['comment']=$res;
		if ($del){
			$data['commentCount']=$this->db->where('wid',$data['id'])->count_all_results('wcomment');
			$this->db->where('id',$data['id'])->update('weibo',['commentCount'=>$data['commentCount']]);
		}
		return $this->_dealData($data);
	}
	
	function getList($limit) {
		$this->db->limit($limit['count'],$limit['page']*$limit['count']);
		switch ($limit['type']){
			case 0:$info=$this->db->query('SELECT SUM(visitCount) sum,COUNT(*) count FROM weibo')->row_array();
			$this->db->where("visitCount>=",floor($info['sum']/($info['count']*3)),FALSE)//平均点击量的1/3
				->order_by('id desc');
				break;
			case 1:
				$this->db->where("authorId IN (SELECT toId FROM attention WHERE fromId=".UID.") OR authorId=".UID,null,FALSE)->order_by('id desc');
				break;
			case 2:
				$this->load->helper('distance');
				$range=GetRange($limit,30000);
				$this->db->where("lat BETWEEN $range[minlat] AND $range[maxlat] AND lng BETWEEN $range[minlng] AND $range[maxlng]",null,FALSE)
					->order_by("time",'desc');
				break;
			case 3:$this->db->where('authorId',$limit['id'])->order_by('id desc');
				break;
			default:errInput();
		}
		$data=$this->db->select('*,(SELECT avatar FROM user WHERE id=weibo.authorId) authorAvatar,(SELECT name FROM user WHERE id=weibo.authorId) authorName')->get('weibo')->result_array();
		array_walk($data, [$this,'_dealData']);
		return $data;
	}
	
	function praise($id) {
		$has=$this->db->where(['wid'=>$id,'uid'=>UID])->get('praise')->num_rows();
		if ($has) return TRUE;
		if (!$this->db->query("UPDATE weibo SET praiseCount=praiseCount+1 WHERE id=?",$id)) return FALSE;
		return $this->db->insert('praise',['uid'=>UID,'wid'=>$id]);
	}
	
	function unPraise($id) {
		$has=$this->db->where(['wid'=>$id,'uid'=>UID])->get('praise')->num_rows();
		if (!$has) return TRUE;
		if (!$this->db->query("UPDATE weibo SET praiseCount=praiseCount-1 WHERE id=?",$id)) return FALSE;
		return $this->db->where(['uid'=>UID,'wid'=>$id])->delete('praise');
	}
	
	function comment($input) {
		$this->load->model('notify');
		if ($input['fid']!=0){
			$t=$this->db->where(['wid'=>$input['wid'],'id'=>$input['fid']])->get('wcomment');
			if (!$t||$t->num_rows()!=1) attack();
			$autId=$t->row_array()['uid'];
			$type=Notify::REPLY;
		}else {
			$autId=$this->db->find('weibo',$input['wid'],'id','authorId')['authorId'];
			$type=Notify::COMMENT;
		}
		$input['uid']=UID;
		$input['time']=time();
		$this->notify->add($type,
				$this->db->find('user',UID,'id','name'),
				$input['wid'],$autId);
		return $this->db->insert('wcomment',$input);
	}
	
	function _dealData(&$data) {
		$data['images']=json_decode(gzuncompress($data['images']),TRUE);
		$data['praiseCount']=$this->db->where('wid',$data['id'])->count_all_results('praise');
		$data['commentCount']=$this->db->where('wid',$data['id'])->count_all_results('wcomment');
		$this->load->model('muser','user');
		if (defined('UID')||$this->user->check())
			$data['praiseStatus']=$this->db->where(['wid'=>$data['id'],'uid'=>UID])->get('praise')->num_rows()==1;
		//SELECT id,name,sign,avatar FROM user WHERE id IN (SELECT uid FROM (SELECT uid FROM praise WHERE wid='2' order by time desc LIMIT 6) as t) 顺序不严格但效率略高
		$data['praiseMember']=$this->db->query("SELECT id,name,sign,avatar FROM user JOIN (SELECT uid,time FROM praise WHERE wid=?) as t ON id=uid ORDER BY t.time desc LIMIT 6",$data['id'])->result_array();
		return $data;
	}
}