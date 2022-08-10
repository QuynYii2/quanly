<?php
require_once 'facebook.php';
class FBCustom {
	private static $appid;
	private static $secret;
	private static $instance;
	private static $facebook;
	
	private function __construct($appid, $secret) {
		#self::$params = JComponentHelper::getParams('com_vjob');
		self::setAppID($appid);
		self::setSecret($secret);
		if ( is_null( self::$facebook ) ) {
			self::$facebook = new Facebook(array( 'appId' => self::$appid, 'secret' => self::$secret ));
		}
	}
	
	static function getInstance($appid, $secret) {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self($appid, $secret);
		}
		return self::$instance;
	}
	
	static function setAppID($appid){
		self::$appid = $appid;
	}
	
	static function setSecret($secret){
		self::$secret = $secret;
	}
	
	static function getLogin($redirect) {
		#if (JRequest::getVar('code')) echo "<script>window.close(); window.opener.location.reload(); </script>";
		$params = array('scope' => 'email, user_birthday, user_about_me, user_hometown, user_location', 'redirect_uri' => $redirect, 'display'=>'popup');
		return self::$facebook->getLoginUrl($params);
	}
	
	static function logout($redirect) {
		$fbvjob_cookie = 'fbsr_' . self::$appid;
		setcookie($fbvjob_cookie, '', time() - 3600, "/");
		
		$token 	= self::$facebook->getAccessToken();
		$params = array('next' => $redirect, 'access_token' => $token);
		$logout = self::$facebook->getLogoutUrl($params);
		self::$facebook->destroySession();
	}
 
	static function getProfile() {
		$user 		= self::$facebook->getUser();
		$profile 	= array();
		if ($user) {
			try {
				$profile= self::$facebook->api('/me');
			} catch (FacebookApiException $e) {
				error_log($e);
				$user = NULL;
			}
		}
		
		return $profile;
	}
}