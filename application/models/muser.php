<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Muser extends CI_Model {
	function checkTel($tel) {
		if (!$tel||!is_numeric($tel)) return FALSE;
		$res=$this->db->find('user',$tel,'tel');
		if (empty($res))
			return TRUE;
		else return FALSE;
	}
	
	function check() {
		$data=getToken();
		$res=$this->db->find('user', $data['id'],'id','token');
		if (empty($res)||$res['token']!=$data['token'])
			return FALSE;
		else{
			define('UID', $data['id']);
			return TRUE;
		}
	}
	
	function login($input) {
		$result =$this->db->find('user', $input['tel'],'tel');
		if (empty($result)) ajax(1001,'此用户不存在！');
		if ($result['password']!==md5(md5($input['password']).'fish')) ajax(1003,'密码错误！');
		$result['token'] = md5(uniqid().rand());
		if ($result['rongToken']==''){
			$this->load->library('rc');
			$token=$this->rc->RCgetToken($result);
			if ($token['status'])
				$result['rongToken']=$token['code'];
			else ajax(2,'融云平台出错！'.$token['code']);
		}
		$result['blogCount']=$this->db->where('authorId',$result['id'])->count_all_results('weibo');
		$result['attentionCount']=$this->db->where('fromId',$result['id'])->count_all_results('attention');
		$result['fansCount']=$this->db->where('toId',$result['id'])->count_all_results('attention');
		$weibo=$this->db->query("SELECT * FROM weibo WHERE authorId=? ORDER BY id desc LIMIT 3",$result['id'])->result_array();
		$result['seed']=array();
		foreach ($weibo as $value) {
			$value['images']=json_decode(gzuncompress($value['images']),TRUE);
			$value['authorAvatar']=$result['avatar'];
			$value['authorName']=$result['name'];
			$value['praiseStatus']=$this->db->query("SELECT * FROM praise WHERE uid=$result[id] AND wid=$value[id]")->num_rows();
			$result['seed'][]=$value;
		}
		$this->db->where('id',$result['id'])->update('user',['token'=>$result['token'],'type'=>0,'rongToken'=>$result['rongToken']]);
		unset($result['password']);unset($result['tel']);
		return $result;
	}
	
	function nearby($input) {
		$this->db->select('id,name,avatar,sign,lat,lng,fans,cared')
			->where(["addrTime>"=>time()-1296000,'id!='=>UID],NULL,FALSE)
			->where("id NOT IN (SELECT toId FROM attention WHERE fromId=".UID.")",NULL,FALSE)
			->order_by("sqrt(lat-$input[lat])+sqrt((lng-$input[lng])*cos((lat+$input[lat])/2))",'desc')
			->limit($input['count'],$input['page']*$input['count']);
		$data=$this->db->get('user')->result_array();
		$this->load->helper('distance');
		foreach ($data as $key => $value) {
			$data[$key]['distance']=GetDistance($data[$key]['lat'],$data[$key]['lng'],$input['lat'],$input['lng']);
		}
		return $data;
	}
}