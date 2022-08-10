<?php
	session_start();
	define("DS", DIRECTORY_SEPARATOR);
	set_include_path("include_path");

	Define("ACTION", 	"admin");
	Define("WRITELOG", 	FALSE);
	
	//ini_set("upload_max_filesize", "20M");
	//ini_set("post_max_size", "100M");

	require_once dirname(dirname(__FILE__)).DS."library".DS."@library.php";
	require_once "configs".DS."@config.php";
	require_once "daos".DS."@daos.php";
	define("LOGS_DIR", BACKEND_LOG_PATH);


    global $FILE;
    if (!empty($_POST["langcode"])) Generals::setSession('langcode', $_POST["langcode"]);
    $smarty->assign("langcode", 	Generals::getSession('langcode', 'en'));
    
    $smarty->config_dir = BACKEND_CONF_PATH.DS.Generals::getSession('langcode', 'en').DS;
	$smarty->config_load("generals.conf");
	
	$option = Generals::getVar('option', 	'index');
	$view	= Generals::getVar('view', 		'list');
	$task	= Generals::getVar('task', 		'display');
	
	$LanguageDao	= new LanguageDao();
	$include_header = BACKEND_TEMPLATE_PATH."include".DS."header".TPL_TYPE;
	$include_footer = BACKEND_TEMPLATE_PATH."include".DS."footer".TPL_TYPE;
	$include_menu 	= BACKEND_TEMPLATE_PATH."include".DS."menu".TPL_TYPE;
	$langlist 		= $LanguageDao->getLanguages(null, 1);
	$messages		= Generals::getError();

	$smarty->assign("include_header", 	$include_header);
	$smarty->assign("include_footer", 	$include_footer);
	$smarty->assign("include_menu",   	$include_menu);
	$smarty->assign("languages",   		$langlist);
	$smarty->assign("messages",   		$messages);

	include(BACKEND_PAGE_PATH."index.php");
?>
