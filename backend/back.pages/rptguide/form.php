<?php
	defined("DS") or die("Errors System");
	require_once 'controler.php';
    $smarty->config_load("rptguide.conf");
	#==================================================================
	#==================================================================
	$cid = Generals::getVar('cid', array());
    $toolbar.= Generals::getToolBar('export', 		Generals::getTitle('TOOLBAR_EXPORT'), 		'export');
	$toolbar.= Generals::getToolBar('apply', 		Generals::getTitle('TOOLBAR_APPLY'), 		'apply');
	$toolbar.= Generals::getToolBar('update', 		Generals::getTitle('TOOLBAR_UPDATE'), 		'save');
	if ($cid[0]) $toolbar.= Generals::getToolBar('delete', Generals::getTitle('TOOLBAR_DELETE'),'delete');
	$toolbar.= Generals::getToolBar('cancel', 		Generals::getTitle('TOOLBAR_CANCEL'), 		'cancel');
	$smarty->assign("toolbar",  $toolbar);
	if ($cid[0]) $smarty->assign("TitlePage", Generals::getTitle('MENU_RPTGUIDE') ." <span>[".Generals::getTitle('MENU_SUB_EDIT')."]</span>");
	else $smarty->assign("TitlePage", Generals::getTitle('MENU_RPTGUIDE') ." <span>[".Generals::getTitle('MENU_SUB_ADD')."]</span>");
	#==================================================================
	#==================================================================
	if ($task):
		$controler = new controler();
		$controler->$task();
	endif;
	#==================================================================
	#==================================================================
    $user	    = Generals::getUserData();
	$ObjectDao 	= new RptGuideDao();
	$ObjectData = $ObjectDao->getData();
	$Ordering 	= $ObjectDao->getOrdering();
    $Quotations = $ObjectDao->getQuotations();

    $ObjectData['fperiod'] = $ObjectData['fperiod'] ? $ObjectData['fperiod'] : date('Y-m-d');
    $ObjectData['tperiod'] = $ObjectData['tperiod'] ? $ObjectData['tperiod'] : date('Y-m-d');
    $ObjectData['introtext'] = is_array($ObjectData['introtext']) ? $ObjectData['introtext'] : unserialize($ObjectData['introtext']);
    #print_r($ObjectData['introtext']);
    #==================================================================
    # Keep Data If Error Handle =======================================
    #==================================================================
    $ObjectData = Generals::getState('data.form') ? Generals::getState('data.form') : $ObjectData;
    #==================================================================
    #==================================================================

	$smarty->assign("ObjectData",   	$ObjectData);
	$smarty->assign("Ordering",  		$Ordering);
    $smarty->assign("Quotations",  		$Quotations);
	$ObjectDao->doClose();
	#==================================================================
	#==================================================================
?>