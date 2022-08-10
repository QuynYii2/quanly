<?php
	defined("DS") or die("Errors System");
	require_once 'controler.php';
	#==================================================================
	#==================================================================
	$toolbar.= Generals::getToolBar('delete', 		Generals::getTitle('TOOLBAR_DELETE'), 		'delete');
	$smarty->assign("toolbar",  $toolbar);
	$smarty->assign("TitlePage", Generals::getTitle('MENU_WRITELOG') ." <span>[".Generals::getTitle('MENU_SUB_LIST')."]</span>");
	$smarty->config_load("writelog.conf");
	#==================================================================
	#==================================================================
	
	if ($task):
		$controler = new controler();
		$controler->$task();
	endif;
	#==================================================================
	#==================================================================
	$ObjectDao 	= new WritelogDao();
	$DataList 	= $ObjectDao->getDataList();
	$DataCount	= $ObjectDao->getCountData();
	$FormSearch	= $ObjectDao->getState();
    $users	    = $ObjectDao->getUsers();
	$page		= Generals::getState('page', 1) ? Generals::getState('page', 1) : 1;

    foreach ($DataList as $key => $items):
        $DataList[$key]['index'] = $key+1;
    endforeach;

	$smarty->assign("paging",   	PagerUtils::PagerSmarty($page, $DataCount, "index.php?option=writelog&view=list", (int)LIMIT_RECORD, (int)PAGES_SIZES));
	$smarty->assign("DataList",  	array_chunk($DataList, 2));
	$smarty->assign("DataCount", 	$DataCount);
	$smarty->assign("FormSearch",  	$FormSearch);
    $smarty->assign("users",  	    $users);
    $smarty->assign("highlight",  	Generals::getVar('highlight'));
    $smarty->assign("pagenum",  	$page);
	#==================================================================
	#==================================================================
?>