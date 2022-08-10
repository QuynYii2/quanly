<?php
	defined("DS") or die("Errors System");
	require_once 'controler.php';
	#==================================================================
	#==================================================================
	$cid = Generals::getVar('cid', array());
	if ($cid[0]) $toolbar.= Generals::getToolBar('delete', Generals::getTitle('TOOLBAR_DELETE'),'delete');
	$toolbar.= Generals::getToolBar('cancel', 		Generals::getTitle('TOOLBAR_CANCEL'), 		'cancel');
	$smarty->assign("toolbar",  $toolbar);
	if ($cid[0]) $smarty->assign("TitlePage", Generals::getTitle('MENU_TOUR') ." <span>[".Generals::getTitle('MENU_SUB_EDIT')."]</span>");
	else $smarty->assign("TitlePage", Generals::getTitle('MENU_TOUR') ." <span>[".Generals::getTitle('MENU_SUB_ADD')."]</span>");
	$smarty->config_load("tour.conf");
    $smarty->config_load("export.conf");
	#==================================================================
	#==================================================================
	if ($task):
		$controler = new controler();
		$controler->$task();
	endif;
	#==================================================================
	#==================================================================
	
	$ObjectDao 	= new QuotationDao('tour');
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