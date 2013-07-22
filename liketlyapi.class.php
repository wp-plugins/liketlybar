<?php
 ################  Liketly Api Class ################
 ################### Version 1.0  ##################
 ########### Author: Liketly.com/liketlyapi/ ######
class LiketLyApi{
	// your Application Domain
	public static $_domain = 'mydomain.com';
	// appurl on liketly
	public static $_appurl = '';
	// appid on liketly
	public static $_appid = '';
	
	public static $_apiserver = 'http://www.liketly.com/';
	private static $_iPage = 0;	
	private static $_sTitle = '';
	private static $_iTotalCalls = 0;
	private static $_aCallHistory = array();
	private static $_oUser = null;

	########### Check if user logged || return Liketly User_id if logged 
	public function CheckLogin($redirect = false, $return = true){
	if(!isset($_SESSION['l_auth_token']) || !$_SESSION['l_auth_token'] || !isset($_SESSION['l_user_id']) || !$_SESSION['l_user_id']) {
		if($redirect){
		// After login, return to same page?
		if($return) $_SESSION['l_return'] = $_SERVER['REQUEST_URI'];
		// redirect to Login/register Page 
		header('location: ' . $this->RegisterLink());
		exit;
		}
		else
		return false;
	}else
	return $_SESSION['l_user_id'];
	}

	########### Do login #######################
	public function DoLogin($key=''){
	$key = $key ? $key : $_GET['key'];
	if(!$key || $this->CheckLogin()) return false;
	$oToken = json_decode(file_get_contents(self::$_apiserver . 'token.php?key=' . $key));
		if(!empty($oToken->token)){
			// save l_auth_token
			$_SESSION['l_auth_token'] = $oToken->token;
			// get user
			$this->user();
			// After login redirect to same page
			if(isset($_SESSION['l_return']) && $_SESSION['l_return']){
			$return = $_SESSION['l_return'];
			unset($_SESSION['l_return']);
			$this->redirect($return);
			}
		}
	}
	###### return user objecr if logged ##########
	public function user()
	{
		if(!self::$_oUser){
		self::$_oUser = self::api('user.get', array(), true);
		$_SESSION['l_user_id'] = isset(self::$_oUser->user_id) ? self::$_oUser->user_id : 0;
		}
		return self::$_oUser;
	}
	########### 3rd Liketly register link ##########
	function registerLink(){
	return self::$_apiserver . '3rdsignup/?domain='.urlencode($this->_domain).'&appid='.$this->_appid.'&language=en';
	}

	######## Api call: see more at http://www.liketly.com/apps/developer/
	######## example: api('user.getUser') ##########
	public  function api($sMethod, $aPost = array(), $bCache = false)
	{		
		// Build the request string we are going to POST to the API server. We include some of the required params.
		$sPost = 'token=' . self::getToken() . '&method=' . $sMethod . '&page=' . self::$_iPage;		
		foreach ($aPost as $sKey => $sValue)
		{
			$sPost .= '&' . $sKey . '=' . $sValue;
		}
	
		if ($bCache && isset($_SESSION['appcache_' . md5($sPost)]))
		{
			return $_SESSION['appcache_' . md5($sPost)];
		}
		
		self::$_iTotalCalls++;
		
	
		// Start curl request.
		$hCurl = curl_init();
	
		// echo '<a href="' . $this->_appid . 'api.php?' . $sPost . '" target="_blank">View Source</a>';
	
		curl_setopt($hCurl, CURLOPT_URL, self::$_apiserver . 'api.php');
		curl_setopt($hCurl, CURLOPT_HEADER, false);
		curl_setopt($hCurl, CURLOPT_RETURNTRANSFER, true);
	
		curl_setopt($hCurl, CURLOPT_SSL_VERIFYPEER, false);
			
		curl_setopt($hCurl, CURLOPT_POST, true);
		curl_setopt($hCurl, CURLOPT_POSTFIELDS, $sPost);
	
		// Run the exec
		$sData = curl_exec($hCurl);
			
		// Close the curl connection
		curl_close($hCurl);
	
		// Return the curl request and since we use JSON we decode it.
		$oJson = json_decode(trim($sData));
		
		if (isset($oJson->api))
		{
			self::$_iPage = (int) $oJson->api->current_page;					
		}
		
		if ($bCache)
		{
			$_SESSION['appcache_' . md5($sPost)] = $oJson->output;
		}
		
		self::$_aCallHistory[] = array(
				'method' => $sMethod,
				'post' => $aPost,
				'return' => $oJson
				);
		
		return $oJson->output;
	}

	public function getToken(){
		if(empty($_SESSION['l_auth_token'])) :
			return false;
		endif;

		return $_SESSION['l_auth_token'];  
	}
	
	### total API requested ######
	public  function getTotalApiCalls()
	{
		return self::$_iTotalCalls;	
	}
	### History of API calles ######
	public  function getApiCalls()
	{
		return self::$_aCallHistory;
	}

	function redirect($url='http://www.liketly.com/'){
	header('location: ' . $url);
	exit;
	}

}

?>