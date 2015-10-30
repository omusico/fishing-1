<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Notify extends CI_Model {
	const COMMENT=100;
	const REPLY=101;
	const PASS=200;
	const FAIL=201;
	const ADD=202;
	const ATTEND=300;
	
	function add($type,$data,$id,$uid=0) {
		$res=['link'=>$id,'uid'=>$uid,'time'=>time(),'type'=>$type];
		switch ($type){
			case Notify::COMMENT:
				$res['msg']=$data['name'].'评价了你的微博';
				break;
			case Notify::REPLY:
				$res['msg']=$data['name'].'回复了你的微博';
				break;
			case Notify::PASS:
				$res['msg']='您的钓点'.$data['name'].'通过了审核';
				break;
			case Notify::FAIL:
				$res['msg']="您的钓点$data[name]因为$data[info]被拒绝了";
				break;
			case Notify::ADD:
				$res['msg']="新增钓点$data[name]";
				break;
			case Notify::ATTEND:
				$res['msg']="$data[name]关注了你";
				break;
			default:attack();
		}
		return $this->db->insert('notify',$res);
	}
}