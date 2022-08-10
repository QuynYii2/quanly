<?php
	defined("DS") or die("Errors System");
	require_once 'controler.php';
    $smarty->config_load("rptagent.conf");
	#==================================================================
	#==================================================================
    $toolbar.= Generals::getToolBar('export', 		Generals::getTitle('TOOLBAR_EXPORT'), 		'export');
	$smarty->assign("toolbar",  $toolbar);
	$smarty->assign("TitlePage", Generals::getTitle('MENU_RPTAGENT') ." <span>[".Generals::getTitle('MENU_SUB_LIST')."]</span>");
	#==================================================================
	#==================================================================
	
	if ($task):
		$controler = new controler();
		$controler->$task();
	endif;
	#==================================================================
	#==================================================================
	$ObjectDao 	= new RptAgentDao();
	$DataList 	= $ObjectDao->getDataList();
	$DataCount	= $ObjectDao->getCountData();
	$FormSearch	= $ObjectDao->getState();
	$page		= Generals::getState('page', 1) ? Generals::getState('page', 1) : 1;

    foreach ($DataList as $key => $items):
        $introtext = json_decode($DataList[$key]['introtext']);
        $items['total'] = 0; $items['paid'] = 0; $items['bank'] = 0;
        if (is_array($introtext->quote)) foreach ($introtext->quote as $intro):
            $items['total'] += $intro->total;
            $items['paid']  += $ObjectDao->getPaidFee($intro->id);
            $items['bank']  += $ObjectDao->getBankFee($intro->id);
        endforeach;
        $DataList[$key]['status'] = Generals::getTitle('OPTION_'.$items['published']);
        $DataList[$key]['balance'] = number_format($items['total'] - $items['paid'], 2, '.', ',');
        $DataList[$key]['total'] = number_format($items['total'], 2, '.', ',');
        $DataList[$key]['paid'] = number_format($items['paid'], 2, '.', ',');
    endforeach;
	
	$smarty->assign("paging",   	PagerUtils::PagerSmarty($page, $DataCount, "index.php?option=rptagent&view=list", (int)LIMIT_RECORD, (int)PAGES_SIZES));
	$smarty->assign("DataList",  	$DataList);
	$smarty->assign("DataCount", 	$DataCount);
	$smarty->assign("FormSearch",  	$FormSearch);
	#==================================================================
	#==================================================================
?>