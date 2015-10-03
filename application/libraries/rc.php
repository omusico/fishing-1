<?php 
	class Rc{

		/**
		 * [RCsignature 生成融云签名]
		 */
		public function RCsignature() {
			
			srand((double)microtime()*1000000);
			
			$appKey = C('RCappKey');
			$appSecret = C('RCappSecert'); // 开发者平台分配的 App Secret。
			$randNumber = rand(); // 获取随机数。
			$timeStamp = time(); // 获取时间戳。
			$signature = sha1($appSecret.$randNumber.$timeStamp);
			
			return array(
				'Content-Type: application/x-www-form-urlencoded;charset=UTF-8',
				'Accept: application/json',
				'App-Key: '.$appKey,
				'Nonce: '.$randNumber,
				'Timestamp: '.$timeStamp,
				'Signature: '.$signature 
			);
		}

		/**
		 * [_getToken 获取Token]
		 * @param  [Array] $userInfo [用户信息]
		 * @return [JSON]			[返回状态码]
		 */
		public function RCgetToken($userInfo){
			
			// 参数
			$params = array(
				'userId' => $userInfo['id'],
				'name' => $userInfo['name'],
				'portraitUri' => $userInfo['avatar']
			);
			
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, 'https://api.cn.rong.io/user/getToken.json');
			// 以返回的形式接收信息
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			// 设置为POST方式
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
			// 不验证https证书
			// curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			// curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_TIMEOUT, 5); // 超时设置
			// 请求头/签名
			curl_setopt($ch, CURLOPT_HTTPHEADER, $this->RCsignature());

			$response = curl_exec($ch);
			curl_close($ch);
			if ($response){
				$response=json_decode($response,true);
				return ($response['code']==200)?array('status'=>TRUE,'code'=>$response['token']):array('status'=>false,'code'=>$response['code']);
			}else return array('status'=>false,'code'=>'');
		}

		/**
		 * [RCrefresh 更新融云信息]
		 * @param [Number] $userId	[用户ID]
		 * @param [String] $userField [用户新名字]
		 */
		public function RCrefreshName($userId, $userName){
			
			// 参数
			$params = array(
				'userId' => $userId,
				'name' => $userName
			);
			
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, 'https://api.cn.rong.io/user/refresh.json');
			// 以返回的形式接收信息
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			// 设置为POST方式
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
			// 不验证https证书
			// curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			// curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_TIMEOUT, 5); // 超时设置
			// 请求头/签名
			curl_setopt($ch, CURLOPT_HTTPHEADER, $this->RCsignature());

			$response = curl_exec($ch);
			curl_close($ch);
			if ($response){
				$response=json_decode($response,true);
				return ($response['code']==200)?true:$response['code'];
			}else return 0;
			
		}

		/**
		 * [RCrefreshFace description]
		 * @param  [Number] $userId   [用户ID]
		 * @param  [String] $userFace [用户新头像]
		 * @return [JSON] 			  [返回状态码]
		 */
		public function RCrefreshFace($userId, $userFace){
			
			// 参数
			$params = array(
				'userId' => $userId,
				'portraitUri' => $userFace
			);
			
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,'https://api.cn.rong.io/user/refresh.json');
			// 以返回的形式接收信息
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			// 设置为POST方式
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
			// 不验证https证书
			// curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			// curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_TIMEOUT, 5); // 超时设置
			// 请求头/签名
			curl_setopt($ch, CURLOPT_HTTPHEADER, $this->RCsignature());

			$response = curl_exec($ch);
			curl_close($ch);
			if ($response){
				$response=json_decode($response,true);
				return ($response['code']==200)?true:$response['code'];
			}else return 0;
		}
	}

?>