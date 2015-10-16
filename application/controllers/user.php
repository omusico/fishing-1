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
			$flag = $this->db->insert('user',['tel'=>$data['tel'],'token'=>uniqid('fish'),
					'password'=>md5(md5($data['password']).'fish')]);
			$flag ?ajax(0, '验证码正确，注册成功!') : busy();
		}else {
			$response==520?ajax(1004, '验证码错误!'):ajax(1, '验证码平台出错!'.$response);
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
			$flag=$this->db->where('id',$token['id'])->update(['password'=>md5(md5($this->input->post('newpwd')).'fish')]);
			if ($flag!==false) ajax(0, '密码修改成功!');
			else busy();
		}else ajax(1001,'此用户不存在！');
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
			$response==520?ajax(1004, '验证码错误!'):ajax(1, '验证码平台出错!'.$response);
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
			$response==520?ajax(1004, '验证码错误!'):ajax(1, '验证码平台出错!'.$response);
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
		$data=$this->input->post(['lat','lng']) OR errInput();
		$data['addrTime']=time();
		$this->db->where('id',UID)->update('user',$data);
		ajax();
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
	
	//获取用户信息
	function getUserinfo(){
		$id=$this->input->post('id');
		$res=$this->db->find('user', $id,'id','id,name,address,age,skill,gender,avatar,sign,fans,cared');
		$res OR ajax(1001,'无此用户');
		if (!$this->m->check())
			$res['relation']=$this->db->where(['fromId'=>UID,'toId'=>$id])->get('attention')->num_rows();
		ajax(0,'',$res);
	}
	
	function getMyinfo(){
		if (!$this->m->check()) noRights();
		$res=$this->db->find('user', UID,'id','id,name,address,age,skill,gender,avatar,sign,token,rongToken,fans,cared');
		ajax(0,'',$res);
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
		ajax(0,'',$res);
	}
	
	function myFans()
	{
		if (!$this->m->check()) noRights();
		$id=$this->input->post('id');
		$id=$id?(int)$id:UID;
		$res=$this->db->where("id in (SELECT fromid FROM attention WHERE toid=$id)",NULL,FALSE)->select('id,name,avatar,sign,fans,cared')->get('user')->result_array();
		ajax(0,'',$res);
	}
}