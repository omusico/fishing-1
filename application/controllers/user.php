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
			ajax(201, '该手机号已被注册!');
		$this->load->helper('mob');
		$response =mobValidate($data['tel'], $data['code']);
		if ($response === true) {
			$flag = $this->db->insert('user',['tel'=>$data['tel'],'token'=>uniqid('fish'),
					'password'=>md5(md5($data['password']).'fish')]);
			$flag ?ajax(200, '验证码正确，注册成功!') : ajax(0, '服务器繁忙，请重试！');
		}else {
			ajax($response, '验证码错误!');
		}
	}
	
	function login(){
		$input=$this->input->post(['tel','password']);
		if (!$input) busy();
		$result =$this->db->find('user', $input['tel'],'tel');
		if (empty($result)) ajax(202,'此用户不存在！');
		if ($result['password']!==md5(md5($info['password']).'fish')) ajax(201,'密码错误！');
		$result['token'] = md5(uniqid().rand());
		$this->db->where('id',$result['id'])->update('user',['token'=>$result['token'],'type'=>0]);
		unset($result['password']);
		ajax(200,'',$result);
	}
	
	function checkTel(){
		$userTel = $this->input->post('tel');
		$this->m->checkTel($userTel) ? ajax(200, '可以注册'): ajax(201, '该手机号已被注册!') ;
	}
	
	function modPass(){
		$token=getToken();
		$data=$this->db->find('user', $token['id'],'id','token,password');
		if (!empty($data)){
			if ($data['token']!=$token['token']) noRights();
			if ($data['password']!=md5(md5(I('post.oldpwd')).'fish')) ajax(201,'原密码错误！');
			$flag=$this->db->where('id',$token['id'])->update(['password'=>md5(md5(I('post.newpwd')).'fish')]);
			if ($flag!==false) ajax(200, '密码修改成功!');
			else busy();
		}else ajax(202,'此用户不存在！');
	}
	
	function bindTel(){
		$data=$this->input->post(['tel','code','password']);
		if (!$data)
			busy();
		$token=getToken();
		$result =$this->db->find('user', $token['id'],'id','token,password');
		if (empty($result))
			busy();
		if ($result['token']!=$token['token']||$result['password']!==md5(md5($data['password']).'fish'))
			ajax(202, '原密码错误！');
		if (!$this->m->checkTel($data['tel']))
			ajax(201, '该手机号已被注册!');
		$this->load->helper('mob');
		$response =mobValidate($data['tel'], $data['code']);
		if ($response === true) {
			$this->db->where('id',$token['id'])->update('user',['tel',$data['tel']])?ajax(200, '验证码正确，注册成功!') : ajax(0, '服务器繁忙，请重试！');
		}else {
			ajax($response, '验证码错误!');
		}
	}
	
	//修改用户信息
	function modInfo(){
		if (!$user->check()) noRights();
		$data=$this->db->field(['name','gender','address','avatar','age','skill','sign'])->create();
		$this->db->where("id",UID)->update('user',$data)?ajax(200, '修改信息成功!'):busy();
	}
	
	//获取用户信息
	function getUserinfo(){
		$id=$this->input->post('id');
		$res=$this->db->find('user', $id,'id','id,name,address,age,skill,gender,avatar,sign');
		empty($res)?ajax(100,'无此用户'):ajax(200,'',$res);
	}
	
	function getMyinfo(){
		if (!$this->m->check()) noRights();
		$res=$this->db->find('user', UID,'id','id,name,address,age,skill,gender,avatar,sign,token');
		ajax(200,'',$res);
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
		ajax(200,'',$res);
	}
}