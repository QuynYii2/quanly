<?php
	global $smarty;
	$smarty = new Smarty();
	$smarty->template_dir 	= BACKEND_TEMPLATE_PATH;
	$smarty->compile_dir 	= BACKEND_TEMPLATE_C_PATH;
	$smarty->cache_dir 		= CACHE_PATH;
	$smarty->debugging = true;
	$smarty->plugins_dir 	= array(SMARTY_DIR . "plugins", SMARTY_DIR."plugins".DS."plugins_ajax");
?>