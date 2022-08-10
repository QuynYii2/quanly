<?php
	defined("DS") or die("Errors System");
	require_once 'controler.php';
	#==================================================================
	#==================================================================
	$cid = Generals::getVar('cid', array());
    #$toolbar.= Generals::getToolBar('status', 		Generals::getTitle('TOOLBAR_UPDATE'), 		'save');
	$toolbar.= Generals::getToolBar('cancel', 		Generals::getTitle('TOOLBAR_CANCEL'), 		'cancel');
	$smarty->assign("toolbar",  $toolbar);
	if ($cid[0]) $smarty->assign("TitlePage", Generals::getTitle('MENU_OPERATION') ." <span>[".Generals::getTitle('MENU_SUB_EDIT')."]</span>");
	else $smarty->assign("TitlePage", Generals::getTitle('MENU_OPERATION') ." <span>[".Generals::getTitle('MENU_SUB_ADD')."]</span>");
	$smarty->config_load("extquotation.conf");
    $smarty->config_load("export.conf");
	#==================================================================
	#==================================================================
	if ($task):
		$controler = new controler();
		$controler->$task();
	endif;
	#==================================================================
	#==================================================================
	
	$ObjectDao 	= new QuotationDao(2);
	$ObjectData = $ObjectDao->getData();
	$Ordering 	= $ObjectDao->getOrdering();
    $persons    = $ObjectDao->getPersons();
    $agencies   = $ObjectDao->getAgencies('0'.$ObjectData['agencies'].'0');
    $locations  = $ObjectDao->getLocations();
    $sergenres  = $ObjectDao->getServiceGenre();
    $journeies  = $ObjectDao->getJourneies();
	$ObjectLang	= array();
    $ObjectData['distext'] = Generals::getTitle('DISGENRE_'.$ObjectData['disgenre']);

    if (is_array($journeies)) foreach ($journeies as $i => $journey):
        if (is_array($journey['services'])) foreach ($journey['services'] as $j => $service):
            $journeies[$i]['services'][$j]['profiles'] = $ObjectDao->getServiceData($service['profile']);
        endforeach;
    endforeach;

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