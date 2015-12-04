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
			$log=json_encode(['url'=>$this->uri->uri_string(),'uid'=>UID]);
			error_log($log."\n",3,APPPATH.'logs/'.date('Y-m-d').'.log');
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
			$result['rongToken']=$token['status']?$token['code']:'';//失败但是要正常登陆
		}
		$this->db->where('id',$result['id'])->update('user',['token'=>$result['token'],'type'=>0,'rongToken'=>$result['rongToken']]);
		return $this->getInfo($result);
	}
	
	function nearby($input) {
		$this->db->select('id,name,avatar,sign,lat,lng,fans,cared')
			->where(["addrTime>"=>time()-1296000,'id!='=>UID],NULL,FALSE)
			->order_by("pow(lat-$input[lat],2)+pow(lng-$input[lng],2)",'asc')
			->limit($input['count'],$input['page']*$input['count']);
		$data=$this->db->get('user')->result_array();
		$this->load->helper('distance');
		foreach ($data as $key => $value) {
// 			$data[$key]['value']=pow($data[$key]['lat']-$input['lat'],2)+pow(($data[$key]['lng']-$input['lng'])*cos(($data[$key]['lat']+$input['lat'])/2),2);
			$data[$key]['distance']=GetDistance($data[$key]['lat'],$data[$key]['lng'],$input['lat'],$input['lng']);
		}
		return $data;
	}
	
	function contact($input) {
		$tel=[];$name=[];
		foreach ($input as $value) {
			$tel[]=$value['phone'];
			$name[$value['phone']]=$value['contact'];
		}
		if (empty($tel)) return [];
		$data=$this->db->select('id,name,avatar,sign,tel')->where_in('tel',$tel)->get('user')->result_array();
		$res=[];
		foreach ($data as $value) {
			$value['relation']=$this->db->where(['fromId'=>UID,'toId'=>$value['id']])->get('attention')->num_rows()==1;
			$value['contactName']=$name[$value['tel']];
			$res[]=$value;
		}
		return $res;
	}
	
	function getInfo($result) {
		$result['blogCount']=$this->db->where('authorId',$result['id'])->count_all_results('weibo');
		$weibo=$this->db->query("SELECT * FROM weibo WHERE authorId=? ORDER BY id desc LIMIT 3",$result['id'])->result_array();
		$result['seed']=array();
		foreach ($weibo as $value) {
			$value['images']=json_decode(gzuncompress($value['images']),TRUE);
			$value['authorAvatar']=$result['avatar'];
			$value['authorName']=$result['name'];
			$value['praiseStatus']=$this->db->query("SELECT * FROM praise WHERE uid=$result[id] AND wid=$value[id]")->num_rows()==1;
			$result['seed'][]=$value;
		}
		unset($result['password']);unset($result['tel']);unset($result['location']);
		return $result;
	}
}