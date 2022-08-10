<?php
	defined('DS') or die("Errors System");

	//Redirect to old page when current session is ended:

	if($_REQUEST["returnPath"] == ""){
		$returnPath = "?";
		//foreach($_GET as $key=>$val){
		//	$returnPath .= $key . "=" . $val . "&";
		//}

		$returnPath = $_SERVER["REQUEST_URI"];
		$queryStringPos = strripos($returnPath, "?");
		$strLength = strlen($returnPath);
		if($queryStringPos !== false){
			$returnPath = substr($returnPath, $queryStringPos, $strLength-$queryStringPos);
		}
		$smarty->assign("returnPath", urlencode(trim($returnPath, '&')));
	}else{
		$smarty->assign("returnPath", $_REQUEST["returnPath"]);
	}
	//END Redirect to old page when current session is ended

	$template = BACKEND_TEMPLATE_PATH.'login'.TPL_TYPE;

	if (file_exists($template)) {
		$smarty->config_load("user.conf");
		$islogin = Generals::getVar('islogin');
        Generals::setSession('BEFORE_LOGIN', JURI::current());

		if (!empty($islogin)) { /*************Click logined*************/
			if (Generals::getSession('_CAPTCHA') == strtolower(Generals::getVar('captcha')) && Generals::getSession('_CAPTCHA')):
				$ObjectDao  = new UserDao();
				if (empty($ObjectData)) $ObjectData = $ObjectDao->getLoginData(Generals::getVar('username'), Generals::getVar('password'));
				#---------------------------------------------------------------------------
				#if ($account_name == CONFIG_USER && $account_pass == CONFIG_PASS) {
				#	if (empty($accountData)) $accountData = array("account_id"=>1,"account_name"=>"ChienKV","account_full_name"=>"Khương Văn Chiện");
				#}
				#---------------------------------------------------------------------------
				if (!empty($ObjectData)) {
					Generals::setSession('LOGIN_DATA', $ObjectData);
					//header("Location: ".Generals::getSession('BEFORE_LOGIN') . "?returnPath=" . $_REQUEST["returnPath"]);
					header("Location: " . urldecode($_REQUEST["returnPath"]));
				}
			endif;
		}
		Generals::getCaptcha(3);
		return $smarty->display($template);
	} else {
		return $smarty->display(BACKEND_TEMPLATE_ERR);// Template not found
	}
?>