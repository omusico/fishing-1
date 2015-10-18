<?php 
	function mobValidate($userTel, $userCode){
		$appKey = 'a5be1fdc254c'; // appKey
		$api = 'https://web.sms.mob.com/sms/verify'; // 请求地址
		// 请求参数
		$params = array(
			'appkey' => $appKey,
			'phone' => $userTel,
			'zone' => '86',
			'code' => $userCode
		);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $api);
		// 以返回的形式接收信息
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		// 设置为POST方式
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
		// 不验证https证书
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_TIMEOUT, 5); // 超时设置
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/x-www-form-urlencoded;charset=UTF-8',
			'Accept: application/json',
		));

		$response = curl_exec($ch);
		curl_close($ch);
		if ($response){
			$response=json_decode($response,true);
			return ($response['status']==200)?true:$response['status'];
		}else return 0;
	}
?>