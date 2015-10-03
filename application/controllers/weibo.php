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
		if (!$data) busy();
		$data['author']=UID;
		$data['time']=time();
		$this->db->insert('weibo',$data);
		ajax();
	}
	
	function test() {
		$comment=array(['id'=>1,'fid'=>0],['id'=>2,'fid'=>1],['id'=>3,'fid'=>1],['id'=>4,'fid'=>2]);
		$link=array();$res=array();
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
		//var_dump($link);
		var_dump($res);
	}
}