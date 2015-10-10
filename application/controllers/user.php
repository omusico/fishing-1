<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class User extends CI_Controller {
	function __construct() {
		parent::__construct();
		$this->load->model('muser','m');
	}
	
	//注册
	function register(){
		$data=$this->db->create('user');
		if (!$this->m->checkTel($data['tel']))
			ajax(1002, '该手机号已被注册!');
		$this->load->helper('mob');
		$response =mobValidate($data['tel'], $data['code']);
		if ($response === true) {
			$flag = $this->db->insert('user',['tel'=>$data['tel'],'token'=>uniqid('fish'),
					'password'=>md5(md5($data['password']).'fish')]);
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
			if ($data['password']!=md5(md5(I('post.oldpwd')).'fish')) ajax(1003,'原密码错误！');
			$flag=$this->db->where('id',$token['id'])->update(['password'=>md5(md5(I('post.newpwd')).'fish')]);
			if ($flag!==false) ajax(0, '密码修改成功!');
			else busy();
		}else ajax(1001,'此用户不存在！');
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
	
	//获取用户信息
	function getUserinfo(){
		$id=$this->input->post('id');
		$res=$this->db->find('user', $id,'id','id,name,address,age,skill,gender,avatar,sign');
		empty($res)?ajax(1001,'无此用户'):ajax(0,'',$res);
	}
	
	function getMyinfo(){
		if (!$this->m->check()) noRights();
		$res=$this->db->find('user', UID,'id','id,name,address,age,skill,gender,avatar,sign,token,rongToken');
		ajax(0,'',$res);
	}
	
	function attend()
	{
		if (!$this->m->check()) noRights();
		$this->db->insert('attention',['fromid'=>UID,'toId'=>$this->input->post('id')]);
		ajax();
	}
	
	function unAttend()
	{
		if (!$this->m->check()) noRights();
		$this->db->where(['fromid'=>UID,'toId'=>$this->input->post('id')])->delete('attention');
		ajax();
	}
	
	function myAttend()
	{
		if (!$this->m->check()) noRights();
		$res=$this->db->where("id in (SELECT uid FROM attention WHERE fromid=?)",UID,FALSE)->select('id,name,avatar')->get('attention')->result_array();
		ajax(0,'',$res);
	}
}