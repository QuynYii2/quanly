<?php
	defined("DS") or die("Errors System");
	require_once 'controler.php';
	#==================================================================
	#==================================================================
	$toolbar.= Generals::getToolBar('publish', 		Generals::getTitle('TOOLBAR_PUBLISH'), 		'publish');
	$toolbar.= Generals::getToolBar('unpublish', 	Generals::getTitle('TOOLBAR_UNPUBLISH'), 	'unpublish');
	$toolbar.= Generals::getToolBar('ordering', 	Generals::getTitle('TOOLBAR_ORDERING'), 	'ordering');
	$toolbar.= Generals::getToolBar('delete', 		Generals::getTitle('TOOLBAR_DELETE'), 		'delete');
	$toolbar.= Generals::getToolBar('create', 		Generals::getTitle('TOOLBAR_CREATE'), 		'create');
	$smarty->assign("toolbar",  $toolbar);
	$smarty->assign("TitlePage", Generals::getTitle('MENU_SUPPLIER') ." <span>[".Generals::getTitle('MENU_SUB_LIST')."]</span>");
	$smarty->config_load("supplier.conf");
	#==================================================================
	#==================================================================
	
	if ($task):
		$controler = new controler();
		$controler->$task();
	endif;
	#==================================================================
	#==================================================================
	$ObjectDao 	= new SupplierDao();
	$DataList 	= $ObjectDao->getDataList();
	$DataCount	= $ObjectDao->getCountData();
	$FormSearch	= $ObjectDao->getState();
    $locations	= $ObjectDao->getLocations();
    $services   = $ObjectDao->getServiceGenre();
	$page		= Generals::getState('page', 1) ? Generals::getState('page', 1) : 1;
	
	$smarty->assign("paging",   	PagerUtils::PagerSmarty($page, $DataCount, "index.php?option=supplier&view=list", (int)LIMIT_RECORD, (int)PAGES_SIZES));
	$smarty->assign("DataList",  	$DataList);
	$smarty->assign("DataCount", 	$DataCount);
	$smarty->assign("FormSearch",  	$FormSearch);
    $smarty->assign("locations",  	$locations);
    $smarty->assign("services",  	$services);
	#==================================================================
	#==================================================================
?>