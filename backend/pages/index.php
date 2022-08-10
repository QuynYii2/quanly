<?php
	defined('DS') or die("Errors System");
	$logindata 	= Generals::getSession('LOGIN_DATA');

	if (empty($logindata)){
		include BACKEND_PAGE_PATH.'login.php';

	}else{
        $UserGroupDao   = new UsergroupDao();
        $permission     = $UserGroupDao->getData($logindata['groupid']);
        $permission["mod_create"] = (array)json_decode($permission['mod_create']);
        $permission["mod_update"] = (array)json_decode($permission['mod_update']);
        $permission["mod_delete"] = (array)json_decode($permission['mod_delete']);
        $permission["mod_simple"] = (array)json_decode($permission['mod_simple']);
        Generals::setSession('permission', $permission);

        $include_view = BACKEND_TEMPLATE_PATH.$option.DS.$view.TPL_TYPE;
		$include_page = BACKEND_PAGE_PATH.$option.DS.$view.'.php';

        if (Generals::getVar('ajax')):
            include(BACKEND_PAGE_PATH.$option.DS.$view.'.php');
            exit();
        endif;

		if (file_exists($include_view)) {
			$smarty->assign("include_option", $include_view);
			$smarty->assign("logindata", $logindata);
			if (file_exists($include_page)) include(BACKEND_PAGE_PATH.$option.DS.$view.'.php');
			return $smarty->display(BACKEND_TEMPLATE_PATH.'index'.TPL_TYPE);
		} else {
			$smarty->assign("logindata", 	$logindata);
			return $smarty->display(BACKEND_TEMPLATE_ERR);
		}
	}//endif;
?>
