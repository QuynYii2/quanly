<?php
	defined("DS") or die("Errors System");
	require_once 'controler.php';
	#==================================================================
	#==================================================================
    /**
	$toolbar.= Generals::getToolBar('publish', 		Generals::getTitle('TOOLBAR_PUBLISH'), 		'publish');
	$toolbar.= Generals::getToolBar('unpublish', 	Generals::getTitle('TOOLBAR_UNPUBLISH'), 	'unpublish');
	$toolbar.= Generals::getToolBar('ordering', 	Generals::getTitle('TOOLBAR_ORDERING'), 	'ordering');
	$toolbar.= Generals::getToolBar('delete', 		Generals::getTitle('TOOLBAR_DELETE'), 		'delete');
	$toolbar.= Generals::getToolBar('create', 		Generals::getTitle('TOOLBAR_CREATE'), 		'create');
    **/
	$smarty->assign("toolbar",  $toolbar);
	$smarty->assign("TitlePage", Generals::getTitle('MENU_REVENUE') ." <span>[".Generals::getTitle('MENU_SUB_LIST')."]</span>");
	$smarty->config_load("extquotation.conf");
	#==================================================================
	#==================================================================
	
	if ($task):
		$controler = new controler();
		$controler->$task();
	endif;
	#==================================================================
	#==================================================================
	$ObjectDao 	= new QuotationDao(3);
    $DataList 	= $ObjectDao->getDataList();
    $DataCount	= $ObjectDao->getCountData();
    $FormSearch	= $ObjectDao->getState();
    $agencies   = $ObjectDao->getAgencies();
    $locations  = $ObjectDao->getLocations();
    $suppliers  = $ObjectDao->getSuppliers();
    $page		= Generals::getState('page', 1) ? Generals::getState('page', 1) : 1;
    if (is_array($DataList)) foreach ($DataList as $key => $items):
        if ($items['agency']):
            $agencies_name = $ObjectDao->getAgencies('0'.$items['agencies'].'0');
            $agencieschunk = array_chunk($agencies_name, 5);
            $agencies_name = $agencieschunk[0];
            foreach ($agencies_name as $i => $_item):
                $agencies_name[$i] = $_item['name'];
            endforeach;
            if (count($agencieschunk) > 1) $agencies_name[5] = '...';
            $DataList[$key]['agencies_name'] = '<b>'.implode('</b>, <b>', $agencies_name).'</b>';
        endif;

        $ObjectDao->prepare('SELECT COUNT(*) AS export FROM tbl_invoice WHERE quotation = ?');
        $export = $ObjectDao->exec(array((int)$items['id']));
        $DataList[$key]['export']   = (int)$export[0]['export'];
        $DataList[$key]['paid']     = 0;

        $payments = $ObjectDao->getPayments(0, $items['id']);
        if (is_array($payments)) foreach ($payments as $pay):
            $DataList[$key]['paid'] += $pay['price'];
        endforeach;
        $DataList[$key]['balance'] = $DataList[$key]['total'] - $DataList[$key]['paid'];
    endforeach;
    $smarty->assign("paging",   	PagerUtils::PagerSmarty($page, $DataCount, "index.php?option=revenue&view=list", (int)LIMIT_RECORD, (int)PAGES_SIZES));
    $smarty->assign("offset",       (int)LIMIT_RECORD*((int)$page-1));
    $smarty->assign("DataList",  	$DataList);
    $smarty->assign("DataCount", 	$DataCount);
    $smarty->assign("FormSearch",  	$FormSearch);
    $smarty->assign("agencies",  	$agencies);
    $smarty->assign("locations",  	$locations);
    $smarty->assign("suppliers",  	$suppliers);
	#==================================================================
	#==================================================================
?>
