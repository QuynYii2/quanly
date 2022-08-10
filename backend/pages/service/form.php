<?php
	defined("DS") or die("Errors System");
	require_once 'controler.php';

    $genre = Generals::getVar('genre');
    if (empty($genre)) Generals::redirect('index.php');
	#==================================================================
	#==================================================================
	$cid = Generals::getVar('cid', array());
	$toolbar.= Generals::getToolBar('apply', 		Generals::getTitle('TOOLBAR_APPLY'), 		'apply');
	$toolbar.= Generals::getToolBar('update', 		Generals::getTitle('TOOLBAR_UPDATE'), 		'save');
    if ($cid[0]):
        $toolbar.= Generals::getToolBar('saveas', Generals::getTitle('TOOLBAR_COPY'),	'copy');
        $toolbar.= Generals::getToolBar('delete', Generals::getTitle('TOOLBAR_DELETE'),	'delete');
    endif;
	$toolbar.= Generals::getToolBar('cancel', 		Generals::getTitle('TOOLBAR_CANCEL'), 		'cancel');
	$smarty->assign("toolbar",  $toolbar);
	if ($cid[0]) $smarty->assign("TitlePage", Generals::getTitle('MENU_SERVICE_'.strtoupper($genre)) ." <span>[".Generals::getTitle('MENU_SUB_EDIT')."]</span>");
	else $smarty->assign("TitlePage", Generals::getTitle('MENU_SERVICE_'.strtoupper($genre)) ." <span>[".Generals::getTitle('MENU_SUB_ADD')."]</span>");
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
	$ObjectData = $ObjectDao->getData();
	$Ordering 	= $ObjectDao->getOrdering();
    $suppliers	= $ObjectDao->getSuppliers();
    $locations	= $ObjectDao->getLocations();
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
    $smarty->assign("suppliers",  	$suppliers);
    $smarty->assign("locations",  	$locations);
    $smarty->assign("genre",  	    $genre);
	$ObjectDao->doClose();
	#==================================================================
	#==================================================================
?>