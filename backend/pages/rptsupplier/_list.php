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

    foreach ($DataList as $key => $items):
        $DataList[$key]['status'] = Generals::getTitle('OPTION_'.$items['published']);
        $DataList[$key]['rf_date'] = date('d-m-Y', strtotime($items['departure']));
        $DataList[$key]['rt_date'] = date('d-m-Y', strtotime('+'.(int)$items['numday'].' day', strtotime($items['departure'])));
        $DataList[$key]['balance'] = number_format($items['total'] - $items['paid'], 2, '.', ',');
        $DataList[$key]['total'] = number_format($items['total'], 2, '.', ',');
        $DataList[$key]['paid'] = number_format($items['paid'], 2, '.', ',');
    endforeach;
	
	$smarty->assign("paging",   	PagerUtils::PagerSmarty($page, $DataCount, "index.php?option=rptsupplier&view=list", (int)LIMIT_RECORD, (int)PAGES_SIZES));
	$smarty->assign("DataList",  	$DataList);
	$smarty->assign("DataCount", 	$DataCount);
	$smarty->assign("FormSearch",  	$FormSearch);
	#==================================================================
	#==================================================================
?>