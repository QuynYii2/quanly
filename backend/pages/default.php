<?php
	defined('DS') or die("Errors System");

	$template = BACKEND_TEMPLATE_ERR;
	
	$smarty->config_load("generals.conf");
	
	$include_header = BACKEND_TEMPLATE_PATH."include".DS."header".TPL_TYPE;
	$include_footer = BACKEND_TEMPLATE_PATH."include".DS."footer".TPL_TYPE;
	$include_left 	= BACKEND_TEMPLATE_PATH."include".DS."left".TPL_TYPE;
	
	$smarty->assign("include_header", 	$include_header);
	$smarty->assign("include_footer", 	$include_footer);
	$smarty->assign("include_left",   	$include_left);
	if (file_exists($template)) {
		return $smarty->display($template);
	} else {
		return $smarty->display(BACKEND_TEMPLATE_ERR);// Template not found
	}
?>
