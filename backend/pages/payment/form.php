<?php
	defined("DS") or die("Errors System");
	require_once 'controler.php';
	#==================================================================
	#==================================================================
	$cid = Generals::getVar('cid', array());
	$toolbar.= Generals::getToolBar('apply', 		Generals::getTitle('TOOLBAR_APPLY'), 		'apply');
	$toolbar.= Generals::getToolBar('update', 		Generals::getTitle('TOOLBAR_UPDATE'), 		'save');
	if ($cid[0]) $toolbar.= Generals::getToolBar('delete', Generals::getTitle('TOOLBAR_DELETE'),'delete');
	$toolbar.= Generals::getToolBar('cancel', 		Generals::getTitle('TOOLBAR_CANCEL'), 		'cancel');
	$smarty->assign("toolbar",  $toolbar);
	if ($cid[0]) $smarty->assign("TitlePage", Generals::getTitle('MENU_PAYMENT') ." <span>[".Generals::getTitle('MENU_SUB_EDIT')."]</span>");
	else $smarty->assign("TitlePage", Generals::getTitle('MENU_PAYMENT') ." <span>[".Generals::getTitle('MENU_SUB_ADD')."]</span>");
	$smarty->config_load("payment.conf");
	#==================================================================
	#==================================================================
	if ($task):
		$controler = new controler();
		$controler->$task();
	endif;
	#==================================================================
	#==================================================================
	
	$ObjectDao 	= new PaymentDao();
	$ObjectData = $ObjectDao->getData();
	$Ordering 	= $ObjectDao->getOrdering();
	$ObjectLang	= array();

	if (is_array($langlist)) foreach ($langlist as $key => $language):
		$ObjectLang[$language['code']] = $ObjectDao->getDataLang($language['code']);
	endforeach;

    #==================================================================
    # Keep Data If Error Handle =======================================
    #==================================================================
    $ObjectData = Generals::getState('data.form') ? Generals::getState('data.form') : $ObjectData;
    if (is_array($ObjectLang)) foreach ($ObjectLang as $code => $items):
        if (is_array($items)) foreach ($items as $key => $val):
            if ($key == 'id' && $ObjectData['oldid'][$code]) $ObjectLang[$code][$key] = $ObjectData['oldid'][$code];
            elseif (!empty($ObjectData[$key][$code])) $ObjectLang[$code][$key] = $ObjectData[$key][$code];
        endforeach;
    endforeach;
    #==================================================================
    #==================================================================

	$smarty->assign("ObjectData",   	$ObjectData);
	$smarty->assign("ObjectLang",  		$ObjectLang);
	$smarty->assign("Ordering",  		$Ordering);
	$ObjectDao->doClose();
	#==================================================================
	#==================================================================
?>