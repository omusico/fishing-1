<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class User extends CI_Controller {
	function __construct() {
		parent::__construct();
		$this->load->model('muser','m');
	}
	
	//注册
	function register(){
		$data=$this->input->post(['tel','code','password']);
		if (!$data) errInput();
		if (!$this->m->checkTel($data['tel']))
			ajax(1002, '该手机号已被注册!');
		$this->load->helper('mob');
		$response =mobValidate($data['tel'], $data['code']);
		if ($response === true) {
			$pics=['http://7xjdz6.com2.z0.glb.qiniucdn.com/S_F29210bdb497e132cc163ad10549de55445c6a8c121473-rHycIk_fw658.jpg',
					'http://7xjdz6.com2.z0.glb.qiniucdn.com/S_F09abe50a71e7dfc1ea1b176a8f827a75fe6df2aa8d00-IASSny_fw658.jpg',
					'http://7xjdz6.com2.z0.glb.qiniucdn.com/S_Fde4f5566b00fdbbee9d12046cb6aa836b7d79f89d723-bB29dD_fw658.jpg',
					'http://7xjdz6.com2.z0.glb.qiniucdn.com/S_Ff78d88972a74fbb5f34dd768c681e1573ff963e234f36-BKYuOG_fw658.jpg',
					'http://7xjdz6.com2.z0.glb.qiniucdn.com/S_Fc6a74619b692f286dc9d2b0dc8636bbb6a2e45971026f-iTXY8S_fw658.jpg',
					'http://7xjdz6.com2.z0.glb.qiniucdn.com/S_Ffc1f7e1c99a0c4bb556b26ad16c6f1bf7d5d969519000-WfoOak_fw658.jpg',
					'http://7xjdz6.com2.z0.glb.qiniucdn.com/S_Faf401a198398e4c1021c463a029e179b9ff556882c50e-i7s57t_fw658.jpg',
					'http://7xjdz6.com2.z0.glb.qiniucdn.com/S_F6e6bc48e4f322b56d8edc3a5fd9db80fcd6e17129fe7-LHN0fm_fw658.jpg',
					'http://7xjdz6.com2.z0.glb.qiniucdn.com/S_Fd61f5ef17ebc553a1612ea4d940d76c3e7b34fd7121ee-ewJZ63_fw658.jpg',
					'http://cdn.t03.pic.sogou.com/3c28af542f2d49f7-8f0182a4cf50287e-917ca3bff74169a2d7f7156483662781_m.jpg',
					'http://7xjdz6.com2.z0.glb.qiniucdn.com/S_F936e52cdda706160358cc0772431002b9cad882d82f3-Y3xOTe_fw658.jpg'];
			$flag = $this->db->insert('user',['tel'=>$data['tel'],'token'=>uniqid('fish'),
					'password'=>md5(md5($data['password']).'fish'),'avatar'=>$pics[array_rand($pics)]]);
			$flag ?ajax(0, '验证码正确，注册成功!') : busy();
		}else {
			$response==468?ajax(1004, '验证码错误!'):ajax(1, '验证码平台出错!'.$response);
		}
	}
	
	function login(){
		$input=$this->input->post(['tel','password']);
		if (!$input) errInput();
		$res=$this->m->login($input);
		ajax(0,'',$res);
	}
	
	function checkTel(){
		$userTel = $this->input->post('tel');
		$this->m->checkTel($userTel) ? ajax(0, '可以注册'): ajax(1002, '该手机号已被注册!') ;
	}
	
	function modPass(){
		$token=getToken();
		$data=$this->db->find('user', $token['id'],'id','token,password');
		if (!empty($data)){
			if ($data['token']!=$token['token']) noRights();
			if ($data['password']!=md5(md5($this->input->post('oldpwd')).'fish')) ajax(1003,'原密码错误！');
			$this->db->where('id',$token['id'])->update('user',['password'=>md5(md5($this->input->post('newpwd')).'fish')])?ajax(0, '密码修改成功!'):busy();
		}else ajax(1001,'此用户不存在！');
	}
	
	function modBg(){
		if (!$this->m->check()) noRights();
		$bg=$this->input->post('bg');
		$bg OR errInput();
		$this->db->where("id",UID)->update('user',['bg'=>$bg])?ajax():busy();
	}
	
	function resetPass() {
		$data=$this->input->post(['tel','code','password']);
		$data OR errInput();
		if ($this->m->checkTel($data['tel']))
			ajax(1001,'此用户不存在！');
		$this->load->helper('mob');
		$response =mobValidate($data['tel'], $data['code']);
		if ($response === true){
			$this->db->where('tel',$data['tel'])->update('user',['password'=>md5(md5($data['password']).'fish')]) OR busy();
		}else {
			$response==468?ajax(1004, '验证码错误!'):ajax(1, '验证码平台出错!'.$response);
		}
	}
	
	function bindTel(){
		$data=$this->input->post(['tel','code','password']);
		if (!$data)
			errInput();
		$token=getToken();
		$result =$this->db->find('user', $token['id'],'id','token,password');
		if (empty($result))
			busy();
		if ($result['token']!=$token['token']||$result['password']!==md5(md5($data['password']).'fish'))
			ajax(1003, '原密码错误！');
		if (!$this->m->checkTel($data['tel']))
			ajax(1002, '该手机号已被注册!');
		$this->load->helper('mob');
		$response =mobValidate($data['tel'], $data['code']);
		if ($response === true) {
			$this->db->where('id',$token['id'])->update('user',['tel',$data['tel']])?ajax(0, '验证码正确，注册成功!') : ajax(0, '服务器繁忙，请重试！');
		}else {
			$response==468?ajax(1004, '验证码错误!'):ajax(1, '验证码平台出错!'.$response);
		}
	}
	
	//修改用户信息
	function modInfo(){
		if (!$this->m->check()) noRights();
		$data=$this->db->field(['name','gender','address','avatar','age','skill','sign'])->create();
		$this->db->where("id",UID)->update('user',$data)?ajax():busy();
	}
	
	function updateAddr() {
		$this->m->check() OR noRights();
		$data=$this->input->post(['lat','lng','location']) OR errInput();
		$data['addrTime']=time();
		$this->db->where('id',UID)->update('user',$data);
		ajax();
	}
	
	function getNotify() {
		$this->m->check() OR noRights();
		$page=$this->input->post('page',FALSE,0);
		$count=$this->input->post('count',FALSE,20);
		$data=$this->db->where('uid='.UID.' OR uid=0',NULL,FALSE)->order_by('id','desc')
			->get('notify',$count,$page*$count)->result_array();
		ajax(0,'',$data);
	}
	
	function getNearby() {
		$this->m->check() OR noRights();
		$input=$this->input->post(['lat','lng']) OR errInput();
		$input['page']=$this->input->post('page',FALSE,0);
		$input['count']=$this->input->post('count',FALSE,10);
		$res=$this->m->nearby($input);
		ajax(0,'',$res);
	}
	
	function findUser() {
		$this->m->check() OR noRights();
		$word=$this->input->post('key') OR errInput();
		$this->db->select('id,name,avatar,sign,fans,cared');
		(is_numeric($word)&&strlen($word)==11)?
		$this->db->where('tel',$word):$this->db->like('name',$word);//根据电话搜索或者根据关键字搜索
		ajax(0,'',$this->db->get('user')->result_array());
	}
	
	function contact() {
		$this->m->check() OR noRights();
		$input=json_decode($this->input->post('data'),TRUE) OR errInput();
		ajax(0,'',$this->m->contact($input));
	}
	
	//获取用户信息
	function getUserinfo(){
		$id=$this->input->post('id');
		$res=$this->db->find('user', $id);
		$res OR ajax(1001,'无此用户');
		if ($this->m->check())
			$res['relation']=$this->db->where(['fromId'=>UID,'toId'=>$id])->get('attention')->num_rows()==1;
		unset($res['token']);unset($res['rongToken']);
		ajax(0,'',$this->m->getInfo($res));
	}
	
	//获取用户头像和昵称
	function getBriefinfo(){
		$id=$this->input->post('id');
		$res=$this->db->find('user', $id,'id','name,avatar');
		$res?ajax(0,'',$res):ajax(1001,'无此用户');
	}
	
	function getMyinfo(){
		if (!$this->m->check()) noRights();
		$res=$this->db->find('user', UID);
		ajax(0,'',$this->m->getInfo($res));
	}
	
	function attend()
	{
		if (!$this->m->check()) noRights();
		$id=$this->input->post('id') OR errInput();
		$this->db->where('id',$id)->step('fans','user');
		$this->db->affected_rows()==1 OR errInput();//此用户不存在
		$this->db->where('id',UID)->step('cared');
		$this->db->insert('attention',['fromid'=>UID,'toId'=>$id]);
		ajax();
	}
	
	function unAttend()
	{
		if (!$this->m->check()) noRights();
		$id=$this->input->post('id') OR errInput();
		$this->db->where('id',$id)->step('fans','user',FALSE);
		$this->db->affected_rows()==1 OR errInput();//此用户不存在
		$this->db->where('id',UID)->step('cared','',FALSE);
		$this->db->where(['fromid'=>UID,'toId'=>$this->input->post('id')])->delete('attention');
		ajax();
	}
	
	function myAttend()
	{
		if (!$this->m->check()) noRights();
		$id=$this->input->post('id');
		$id=$id?(int)$id:UID;
		$res=$this->db->where("id in (SELECT toid FROM attention WHERE fromid=$id)",NULL,FALSE)->select('id,name,avatar,sign,fans,cared')->get('user')->result_array();
		foreach ($res as $key => $value) {
			$res[$key]['relation']=$this->db->where(['fromId'=>UID,'toId'=>$value['id']])->get('attention')->num_rows()==1;
		}
		ajax(0,'',$res);
	}
	
	function myFans()
	{
		if (!$this->m->check()) noRights();
		$id=$this->input->post('id');
		$id=$id?(int)$id:UID;
		$res=$this->db->where("id in (SELECT fromid FROM attention WHERE toid=$id)",NULL,FALSE)->select('id,name,avatar,sign,fans,cared')->get('user')->result_array();
		foreach ($res as $key => $value) {
			$res[$key]['relation']=$this->db->where(['fromId'=>UID,'toId'=>$value['id']])->get('attention')->num_rows()==1;
		}
		ajax(0,'',$res);
	}
}