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
	if ($cid[0]) $smarty->assign("TitlePage", Generals::getTitle('MENU_USERGROUP') ." <span>[".Generals::getTitle('MENU_SUB_EDIT')."]</span>");
	else $smarty->assign("TitlePage", Generals::getTitle('MENU_USERGROUP') ." <span>[".Generals::getTitle('MENU_SUB_ADD')."]</span>");
	$smarty->config_load("usergroup.conf");
	#==================================================================
	#==================================================================
	if ($task):
		$controler = new controler();
		$controler->$task();
	endif;
	#==================================================================
	#==================================================================
	
	$ObjectDao 	= new UsergroupDao();
	$ObjectData = $ObjectDao->getData();
	$Ordering 	= $ObjectDao->getOrdering();

    $modules    = array();
    $folders    = JFolder::folders(BACKEND_PAGE_PATH);
    if (is_array($modules)) foreach ($folders as $folder):
        if ($folder !='index') $modules[$folder] = Generals::getTitle('MENU_'.strtoupper($folder));
    endforeach;

    #==================================================================
    # Keep Data If Error Handle =======================================
    #==================================================================
    $ObjectData = Generals::getState('data.form') ? Generals::getState('data.form') : $ObjectData;
    #==================================================================
    #==================================================================

	$smarty->assign("ObjectData",   	$ObjectData);
	$smarty->assign("Ordering",  		$Ordering);
    $smarty->assign("modules",  		$modules);
    $smarty->assign("mod_simple",  		(array)json_decode($ObjectData['mod_simple']));
    $smarty->assign("mod_detail",  		(array)json_decode($ObjectData['mod_detail']));
    $smarty->assign("mod_create",  		(array)json_decode($ObjectData['mod_create']));
    $smarty->assign("mod_update",  		(array)json_decode($ObjectData['mod_update']));
    $smarty->assign("mod_delete",  		(array)json_decode($ObjectData['mod_delete']));
	$ObjectDao->doClose();
	#==================================================================
	#==================================================================
?>