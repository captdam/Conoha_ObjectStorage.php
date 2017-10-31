<?php
	/** For Conoha Object Storage Server ONLY **/
	class Conoha {
		//Setup here
		private $username = 'YOUR_USERNAME';
		private $password = 'YOUR_PASSWORD';
		private $tenantId = 'YOUR_TENANT_ID';
		//End of setup
		private $token; #Token
		private $endpoint; #End point
		//Constructor, ask for token
		public function ini($token,$expire) {
			//Token expire, request new
			if ($expire < time()) {
				$newToken = $this->renewToken();
				if ($newToken) { #Update token required
					$this->token = $token;
					return $newToken;
				}
				else return false; #Fail
			}
			//Token OK (not expired)
			else {
				$this->token = $token;
				$this->endpoint = 'https://object-storage.tyo1.conoha.io/v1/nc_'.$this->tenantId.'/';
				return true;
			}
			//Notice: as long as the token did not expire (24hrs), it is possible to have more than one active token
		}
		private function renewToken() {
			//Prepare request
			$curl = curl_init();
			$header = array('Content-Type: application/json');
			$data = array(
				'auth' => array(
					'passwordCredentials' => array(
						'username' => $this->username,
						'password' => $this->password
					),
					'tenantId' => $this->tenantId
				)
			);
			$option = array(
				CURLOPT_HTTPHEADER => $header,
				CURLOPT_URL => 'https://identity.tyo1.conoha.io/v2.0/tokens',
				CURLOPT_POST => true,
				CURLOPT_POSTFIELDS => json_encode($data),
				CURLOPT_RETURNTRANSFER => true,
			);
			curl_setopt_array($curl,$option);
			//Send request
			$body = curl_exec($curl);
			if (curl_error($curl)) return false; #Send request fail
			$status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
			if(curl_errno($curl) || $status_code != 200) return false; #Server return error
			$authinfo = json_decode($body); #Decode response
			return $authinfo->access->token->id;
		}
		//PUT
		public function put($url,$content=null,$stream=true) {
			//Create stream
			if ($content) {
				if (!$stream) {
					$temp = tmpfile();
					fwrite($temp,$content);
					fseek($temp,0);
					$content = $temp;
				}
				else $content = fopen($content,'r');	
			}
			//Prepare request
			$curl = curl_init();
			$header = array();
			$header = array(
				'X-Auth-Token: '.$this->token
			);
			if ($content) $options = array( #Create object
				CURLOPT_PUT => true,
				CURLOPT_INFILE => $content,
				CURLOPT_URL => $this->endpoint.$url,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_HTTPHEADER => $header
			);
			else $options = array( #Create container
				CURLOPT_PUT => true,
				CURLOPT_URL => $this->endpoint.$url,
				CURLOPT_VERBOSE => true,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_HTTPHEADER => $header
			);
			curl_setopt_array($curl, $options);
			//Send request
			$body = curl_exec($curl);
			return array(curl_getinfo($curl, CURLINFO_HTTP_CODE),$body);
			
		}
		//GET
		public function get($url) {
			//Prepare request
			$curl = curl_init();
			$header = array(
				'X-Auth-Token: '.$this->token
			);
			$options = array(
				CURLOPT_URL => $this->endpoint.$url,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_HTTPHEADER => $header
			);
			curl_setopt_array($curl, $options);
			//Send request
			$body = curl_exec($curl);
			return array(curl_getinfo($curl, CURLINFO_HTTP_CODE),$body);
		}
		//DELETE
		public function delete($url) {
			//Prepare request
			$curl = curl_init();
			$header = array(
				'X-Auth-Token: '.$this->token
			);
			$options = array(
				CURLOPT_CUSTOMREQUEST => 'DELETE',
				CURLOPT_URL => $this->endpoint.$url,
				CURLOPT_VERBOSE => true,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_HTTPHEADER => $header
			);
			curl_setopt_array($curl, $options);
			//Send request
			$body = curl_exec($curl);
			return array(curl_getinfo($curl, CURLINFO_HTTP_CODE),$body);
		}
	}
?>
