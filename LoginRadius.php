<?php 

class LoginRadius { 
  public $IsAuthenticated, $JsonResponse, $UserProfile, $IsAuth, $UserAuth; 
  
  /** 
   * Get user profile data from LoginRadius. 
  */	
  public function loginradius_get_data($ApiSecrete) {
    $IsAuthenticated = false;
	if (isset($_REQUEST['token'])) {
	  $ValidateUrl = "https://hub.loginradius.com/userprofile.ashx?token=".$_REQUEST['token']."&apisecrete=".$ApiSecrete."";
	  $JsonResponse = $this->loginradius_call_api($ValidateUrl);
	  $UserProfile = json_decode($JsonResponse);
	  if (isset($UserProfile->ID) && $UserProfile->ID != ''){ 
	    $this->IsAuthenticated = true;
		return $UserProfile;
	  }
	}
  }
  
  /** 
	 * Get social login interface parameters from LoginRadius. 
	 */	 
	/* public function loginradius_get_auth($ApiKey, $ApiSecrete){
	   $IsAuth = false;
	   if(empty($ApiKey) || empty($ApiSecrete) || !preg_match('/^\{?[A-Z0-9]{8}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{12}\}?$/i', $ApiSecrete) || !preg_match('/^\{?[A-Z0-9]{8}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{12}\}?$/i', $ApiKey)) {
	   return "invalid";
	   }
	   if (isset($ApiKey)) {
	     $ApiKey = trim($ApiKey);
		 $ApiSecrete = trim($ApiSecrete);
		 $ValidateUrl = "https://hub.loginradius.com/ping/$apikey/$apisecret";
		 $JsonResponse = $this->loginradius_call_api($ValidateUrl);
		 $UserAuth = json_decode($JsonResponse);
		 if (isset($UserAuth->ok) ) {
           return "connection working";
         }
         elseif ($UserAuth == "service connection timeout") {
           return "service connection timeout";
         }
         elseif ($UserAuth == "timeout") {
           return "timeout";
         }
         else {
           return "connection error";
         }
	   }
	 }*/

  public function loginradius_call_api($ValidateUrl) {
    $useapi= Configuration::get('CURL_REQ');
	if ($useapi=='0') {
	  if (in_array ('curl', get_loaded_extensions ()) AND function_exists('curl_exec')) {
	    $curl_handle = curl_init();
		curl_setopt($curl_handle, CURLOPT_URL, $ValidateUrl);
		curl_setopt($curl_handle, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt($curl_handle, CURLOPT_TIMEOUT, 5); 
		curl_setopt($curl_handle, CURLOPT_SSL_VERIFYPEER, false);
		  if (ini_get('open_basedir') == '' && (ini_get('safe_mode') == 'Off' or !ini_get('safe_mode'))) {
			curl_setopt($curl_handle, CURLOPT_FOLLOWLOCATION, 1);
			curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);
			$JsonResponse = curl_exec($curl_handle);
		  }
		  else {
			curl_setopt($curl_handle, CURLOPT_HEADER, 1);
			$url = curl_getinfo($curl_handle, CURLINFO_EFFECTIVE_URL);
			curl_close($curl_handle);
			$curl_handle = curl_init();
			$url = str_replace('?','/?',$url);
			curl_setopt($curl_handle, CURLOPT_URL, $url);
			curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);
			$JsonResponse = curl_exec($curl_handle);
		  }
		$httpCode = curl_getinfo($curl_handle, CURLINFO_HTTP_CODE);
		if(in_array($httpCode, array(400, 401, 403, 404, 500, 503)) && $httpCode != 200){
		  return "service error";
		}else{
		  if(curl_errno($curl_handle) == 28){
		    return "timeout";
		  }
		}
		curl_close($curl_handle);
        }
	  }
	  else {
	    $JsonResponse = @file_get_contents($ValidateUrl);
		if(strpos(@$http_response_header[0], "400") !== false || strpos(@$http_response_header[0], "401") !== false || strpos(@$http_response_header[0], "403") !== false || strpos(@$http_response_header[0], "404") !== false || strpos(@$http_response_header[0], "500") !== false || strpos(@$http_response_header[0], "503") !== false){
				return "service error";
	    }
	  }
	  return $JsonResponse;
	  }
}?>