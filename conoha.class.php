<?php
	/** For Conoha Object Storage Server ONLY **/
	class Conoha {
		private $token;
		private $tenant;
		private $endpoint;
		
		function __construct($tenant,$token=null) {
			$this->tenant = $tenant;
			$this->endpoint = 'https://object-storage.tyo1.conoha.io/v1/nc_'.$tenant.'/';
			$this->token = $token;
		}
		
		//Auth: Get token
		//$auth = array('username'=>username, 'password'=>password)
		public function register($auth) {
			$auth = array('auth' => array(
				'passwordCredentials'	=> array(
					'username'	=> $auth['username'] ?? '',
					'password'	=> $auth['password'] ?? ''
				),
				'tenantId'		=> $this->tenant
			));
			
			$curl = curl_init();
			$request = array(
				CURLOPT_HTTPHEADER	=> array('Content-Type: application/json'),
				CURLOPT_URL		=> 'https://identity.tyo1.conoha.io/v2.0/tokens',
				CURLOPT_POST		=> true,
				CURLOPT_POSTFIELDS	=> json_encode($auth),
				CURLOPT_RETURNTRANSFER	=> true
			);
			curl_setopt_array($curl,$request);
			
			$response = curl_exec($curl);
			if (curl_error($curl))
				throw new Exception('Fail to renew token: '.curl_error($curl));
			
			$responseStatus = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			if ( curl_errno($curl) || $responseStatus != 200 )
				throw new Exception('Fail to renew token. CURL-'.curl_errno($curl).' HTTP-'.$responseStatus);
			
			$response = json_decode($response,true);
			if (!$response)
				throw new Exception ('Bad return data.');
			
			$this->token = $response['access']['token']['id'];
			return $this->token;
		}

		//PUT
                //To create a container: $url = 'container_name'
                //To save a file, $url = 'url', $content = '/local/direction/to/file'
                //To save an string/blob, $url = 'url', $content = 'string/blob', $stream = false
		public function put($url,$content=null,$stream=true) {
			if ($content) {
				if (!$stream) {
					$temp = tmpfile();
					fwrite($temp,$content);
					fseek($temp,0);
					$content = $temp;
				}
				else $content = fopen($content,'r');
			}
			
			return $this->com('PUT',$url,$content);
		}

		//GET
                //$url: URL to resource, just the local URL, do not include beginning '/', for example 'photo/myphoto.png'
		public function get($url) {
			return $this->com('GET',$url);
		}

		//DELETE
                //$url: URL to resource, just the local URL, do not include beginning '/', for example 'photo/myphoto.png'
		public function delete($url) {
			return $this->com('DELETE',$url);
		}
		
		private function com($method,$url,$post=null) {
			$curl = curl_init();
			$request = array(
				CURLOPT_URL		=> $this->endpoint.$url,
				CURLOPT_HTTPHEADER	=> array('X-Auth-Token: '.$this->token),
				CURLOPT_RETURNTRANSFER	=> true
			);
			switch($method) {
				case 'GET':
					/* GET by default */
					break;
				case 'PUT':
					$request[CURLOPT_PUT] = true;
					if ($post)
						$request[CURLOPT_INFILE] = $post;
					break;
				default:
					$request[CURLOPT_CUSTOMREQUEST] = $method;
			}
			
			curl_setopt_array($curl, $request);
			$response = curl_exec($curl);
			if (curl_error($curl))
                                throw new Exception(curl_error($curl));
			
			return array(curl_getinfo($curl, CURLINFO_HTTP_CODE),$response);
		}
	}
?>
