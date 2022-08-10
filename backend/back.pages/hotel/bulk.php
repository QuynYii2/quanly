<?php
	defined("DS") or die("Errors System");
	require_once 'controler.php';
	#==================================================================
	#==================================================================
    $smarty->config_load("hotel.conf");
    $toolbar.= Generals::getToolBar('export1', 		Generals::getTitle('TOOLBAR_EXPORT1'), 		'export');
    $toolbar.= Generals::getToolBar('export2', 		Generals::getTitle('TOOLBAR_EXPORT2'), 		'export');
	$toolbar.= Generals::getToolBar('publish', 		Generals::getTitle('TOOLBAR_PUBLISH'), 		'publish');
	$toolbar.= Generals::getToolBar('unpublish', 	Generals::getTitle('TOOLBAR_UNPUBLISH'), 	'unpublish');
	$toolbar.= Generals::getToolBar('ordering', 	Generals::getTitle('TOOLBAR_ORDERING'), 	'ordering');
	$toolbar.= Generals::getToolBar('delete', 		Generals::getTitle('TOOLBAR_DELETE'), 		'delete');
	$toolbar.= Generals::getToolBar('create', 		Generals::getTitle('TOOLBAR_CREATE'), 		'create');
	$smarty->assign("toolbar",  $toolbar);
	$smarty->assign("TitlePage", Generals::getTitle('MENU_HOTEL') ." <span>[".Generals::getTitle('MENU_SUB_LIST')."]</span>");
	#==================================================================
	#==================================================================
	
	if ($task):
		$controler = new controler();
		$controler->$task();
	endif;
	#==================================================================
	#==================================================================
	$ObjectDao 	= new HotelDao();
	$DataList 	= $ObjectDao->getDataList();
	$DataCount	= $ObjectDao->getCountData();
	$FormSearch	= $ObjectDao->getState();
    $suppliers	= $ObjectDao->getSuppliers();
    $locations	= $ObjectDao->getLocations();
    $services	= $ObjectDao->getServices();
    $seasons	= $ObjectDao->getSeasons();
	$page		= Generals::getState('page', 1) ? Generals::getState('page', 1) : 1;

	$smarty->assign("paging",   	PagerUtils::PagerSmarty($page, $DataCount, "index.php?option=hotel&view=list", (int)LIMIT_RECORD, (int)PAGES_SIZES));
	$smarty->assign("DataList",  	$DataList);
	$smarty->assign("DataCount", 	$DataCount);
	$smarty->assign("FormSearch",  	$FormSearch);
    $smarty->assign("suppliers",  	$suppliers);
    $smarty->assign("locations",  	$locations);
    $smarty->assign("services",  	$services);
    $smarty->assign("seasons",  	$seasons);
	#==================================================================
	#==================================================================
?>