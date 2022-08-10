<?php
	defined("DS") or die("Errors System");
	require_once LIB_PATH."joomla".DS."filesystem".DS."folder.php";
	require_once LIB_PATH."joomla".DS."filesystem".DS."file.php";
	require_once 'controler.php';
	#==================================================================
	#==================================================================
	$smarty->assign("TitlePage", Generals::getTitle('MENU_FIMAGE') ." <span>[".Generals::getTitle('MENU_SUB_LIST')."]</span>");
	$smarty->config_load("fimage.conf");
	#==================================================================
	#==================================================================
	$controler = new controler();
	$controler->$task();
	#==================================================================
	#==================================================================
	$smarty->assign("treefolder",  $controler->getTreeFolder(DATA_PATH.DS.'fckeditor'));
	$smarty->assign('currentlink', Generals::getCurrentPageURL());
	#==================================================================
	#==================================================================
?>