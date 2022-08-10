<?php
	defined("DS") or die("Errors System");
	require_once 'controler.php';
    $smarty->config_load("expguide.conf");
	#==================================================================
	#==================================================================
	$cid = Generals::getVar('cid', array());
    $toolbar.= Generals::getToolBar('export', 		Generals::getTitle('TOOLBAR_EXPORT'), 		'export');
	$toolbar.= Generals::getToolBar('apply', 		Generals::getTitle('TOOLBAR_APPLY'), 		'apply');
	$toolbar.= Generals::getToolBar('update', 		Generals::getTitle('TOOLBAR_UPDATE'), 		'save');
	if ($cid[0]) $toolbar.= Generals::getToolBar('delete', Generals::getTitle('TOOLBAR_DELETE'),'delete');
	$toolbar.= Generals::getToolBar('cancel', 		Generals::getTitle('TOOLBAR_CANCEL'), 		'cancel');
	$smarty->assign("toolbar",  $toolbar);
	if ($cid[0]) $smarty->assign("TitlePage", Generals::getTitle('MENU_EXPGUIDE') ." <span>[".Generals::getTitle('MENU_SUB_EDIT')."]</span>");
	else $smarty->assign("TitlePage", Generals::getTitle('MENU_EXPGUIDE') ." <span>[".Generals::getTitle('MENU_SUB_ADD')."]</span>");
	#==================================================================
	#==================================================================
	if ($task):
		$controler = new controler();
		$controler->$task();
	endif;
	#==================================================================
	#==================================================================
    $user	    = Generals::getUserData();
	$ObjectDao 	= new ExpGuideDao();
	$ObjectData = $ObjectDao->getData();
	$Ordering 	= $ObjectDao->getOrdering();
    $Quotations = $ObjectDao->getQuotations();

    $ObjectData['issued_by'] = $ObjectData['issued_by'] ? $ObjectData['issued_by'] : $user['name'];
    $ObjectData['issued_on'] = $ObjectData['issued_on'] ? $ObjectData['issued_on'] : date('Y-m-d');
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