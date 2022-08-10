<?php
	defined("DS") or die("Errors System");
	require_once 'controler.php';
	#==================================================================
	#==================================================================
	$cid = Generals::getVar('cid', array());
	$toolbar.= Generals::getToolBar('cancel', 		Generals::getTitle('TOOLBAR_CANCEL'), 		'cancel');
	$smarty->assign("toolbar",  $toolbar);
	$smarty->assign("TitlePage", Generals::getTitle('MENU_PREQUOTATION') ." <span>[".Generals::getTitle('MENU_SUB_DETAIL')."]</span>");
	$smarty->config_load("prequotation.conf");
    $smarty->config_load("export.conf");
	#==================================================================
	#==================================================================
	if ($task):
		$controler = new controler();
		$controler->$task();
	endif;
	#==================================================================
	#==================================================================
	
	$ObjectDao 	= new QuotationDao('prequotation');
	$ObjectData = $ObjectDao->getData();
	$Ordering 	= $ObjectDao->getOrdering();
    $persons    = $ObjectDao->getPersons();
    $agencies   = $ObjectDao->getAgencies('0'.$ObjectData['agencies'].'0');
    $locations  = $ObjectDao->getLocations();
    $sergenres  = $ObjectDao->getServiceGenre();
    $journeies  = $ObjectDao->getJourneies();
	$ObjectLang	= array();

    if (!is_array($ObjectData['agencies'])) $ObjectData['agencies'] = explode(',', $ObjectData['agencies']);
	if (is_array($langlist)) foreach ($langlist as $key => $language):
		$ObjectLang[$language['code']] = $ObjectDao->getDataLang($language['code']);
	endforeach;

	$smarty->assign("ObjectData",   	$ObjectData);
	$smarty->assign("ObjectLang",  		$ObjectLang);
	$smarty->assign("Ordering",  		$Ordering);
    $smarty->assign("persons",  		$persons);
    $smarty->assign("agencies",  		$agencies);
    $smarty->assign("locations",  		$locations);
    $smarty->assign("sergenres",  		$sergenres);
    $smarty->assign("journeies",  		$journeies);
	$ObjectDao->doClose();
	#==================================================================
	#==================================================================
?>