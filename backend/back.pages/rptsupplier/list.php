<?php
	defined("DS") or die("Errors System");
	require_once 'controler.php';
    $smarty->config_load("rptsupplier.conf");
	#==================================================================
	#==================================================================
    $toolbar.= Generals::getToolBar('export', 		Generals::getTitle('TOOLBAR_EXPORT'), 		'export');
	$smarty->assign("toolbar",  $toolbar);
	$smarty->assign("TitlePage", Generals::getTitle('MENU_RPTSUPPLIER') ." <span>[".Generals::getTitle('MENU_SUB_LIST')."]</span>");
	#==================================================================
	#==================================================================
	
	if ($task):
		$controler = new controler();
		$controler->$task();
	endif;
	#==================================================================
	#==================================================================
	$ObjectDao 	= new RptSupplierDao();
	$DataList 	= $ObjectDao->getDataList();
	$DataCount	= $ObjectDao->getCountData();
	$FormSearch	= $ObjectDao->getState();
	$page		= Generals::getState('page', 1) ? Generals::getState('page', 1) : 1;

	$smarty->assign("paging",   	PagerUtils::PagerSmarty($page, $DataCount, "index.php?option=rptsupplier&view=list", (int)LIMIT_RECORD, (int)PAGES_SIZES));
	$smarty->assign("DataList",  	$DataList);
	$smarty->assign("DataCount", 	$DataCount);
	$smarty->assign("FormSearch",  	$FormSearch);
	#==================================================================
	#==================================================================
?>