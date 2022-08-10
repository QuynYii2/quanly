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
	if ($cid[0]) $smarty->assign("TitlePage", Generals::getTitle('MENU_EXTQUOTATION') ." <span>[".Generals::getTitle('MENU_SUB_EDIT')."]</span>");
	else $smarty->assign("TitlePage", Generals::getTitle('MENU_EXTQUOTATION') ." <span>[".Generals::getTitle('MENU_SUB_ADD')."]</span>");
	$smarty->config_load("extquotation.conf");
	#==================================================================
	#==================================================================
	if ($task):
        #if (in_array($task, array('export', 'sendto')))
        #    $smarty->config_load("export.conf");

		$controler = new controler();
		$controler->$task();
	endif;
	#==================================================================
	#==================================================================
	
	$ObjectDao 	= new QuotationDao(2);
	$ObjectData = $ObjectDao->getData();
	$Ordering 	= $ObjectDao->getOrdering();
    $persons    = $ObjectDao->getPersons();
    $agencies   = $ObjectDao->getAgencies();
    $locations  = $ObjectDao->getLocations();
    $sergenres  = $ObjectDao->getServiceGenre();
    $journeies  = $ObjectDao->getJourneies();
    $paxgroups  = $ObjectDao->getPersons();
	$ObjectLang	= array();

    if (!is_array($ObjectData['agencies'])) $ObjectData['agencies'] = explode(',', $ObjectData['agencies']);
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
    $smarty->assign("persons",  		$persons);
    $smarty->assign("agencies",  		$agencies);
    $smarty->assign("locations",  		$locations);
    $smarty->assign("sergenres",  		$sergenres);
    $smarty->assign("journeies",  		$journeies);
    $smarty->assign("paxgroups",  		$paxgroups);
	$ObjectDao->doClose();
	#==================================================================
	#==================================================================
?>