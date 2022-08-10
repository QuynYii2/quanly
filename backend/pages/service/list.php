<?php
	defined("DS") or die("Errors System");
	require_once 'controler.php';

    $genre = Generals::getVar('genre');
    if (empty($genre)) Generals::redirect('index.php');
	#==================================================================
	#==================================================================
	$toolbar.= Generals::getToolBar('publish', 		Generals::getTitle('TOOLBAR_PUBLISH'), 		'publish');
	$toolbar.= Generals::getToolBar('unpublish', 	Generals::getTitle('TOOLBAR_UNPUBLISH'), 	'unpublish');
	$toolbar.= Generals::getToolBar('ordering', 	Generals::getTitle('TOOLBAR_ORDERING'), 	'ordering');
	$toolbar.= Generals::getToolBar('delete', 		Generals::getTitle('TOOLBAR_DELETE'), 		'delete');
	$toolbar.= Generals::getToolBar('create', 		Generals::getTitle('TOOLBAR_CREATE'), 		'create');
	$smarty->assign("toolbar",  $toolbar);
	$smarty->assign("TitlePage", Generals::getTitle('MENU_SERVICE_'.strtoupper($genre)) ." <span>[".Generals::getTitle('MENU_SUB_LIST')."]</span>");
	$smarty->config_load("service.conf");
	#==================================================================
	#==================================================================
	
	if ($task):
		$controler = new controler();
		$controler->$task();
	endif;
	#==================================================================
	#==================================================================
	$ObjectDao 	= new ServiceDao();
	$DataList 	= $ObjectDao->getDataList();
	$DataCount	= $ObjectDao->getCountData();
	$FormSearch	= $ObjectDao->getState();
    $suppliers	= $ObjectDao->getSuppliers();
    $locations	= $ObjectDao->getLocations();
	$page		= Generals::getState('page', 1) ? Generals::getState('page', 1) : 1;
	
	$smarty->assign("paging",   	PagerUtils::PagerSmarty($page, $DataCount, "index.php?option=service&genre=".$genre."&view=list", (int)LIMIT_RECORD, (int)PAGES_SIZES));
	$smarty->assign("DataList",  	$DataList);
	$smarty->assign("DataCount", 	$DataCount);
	$smarty->assign("FormSearch",  	$FormSearch);
    $smarty->assign("suppliers",  	$suppliers);
    $smarty->assign("locations",  	$locations);
    $smarty->assign("genre",  	    $genre);
	#==================================================================
	#==================================================================
?>