<?php
	defined("DS") or die("Errors System");
	require_once 'controler.php';
	#==================================================================
	#==================================================================
	$cid = Generals::getVar('cid', array());
	$toolbar.= Generals::getToolBar('update', 		Generals::getTitle('TOOLBAR_UPDATE'), 		'save');
	$smarty->assign("toolbar",  $toolbar);
	$smarty->assign("TitlePage", Generals::getTitle('MENU_CONFIG'));
	#==================================================================
	#==================================================================
	if ($task):
		$controler = new controler();
		$controler->$task();
	endif;
	#==================================================================
	#==================================================================
	
	$ObjectDao 	= new ConfigDao();
    $globals	= $ObjectDao->getGlobals();
	$ObjectList	= $ObjectDao->getOtherConfig();
	$ImagesList	= $ObjectDao->getImageConfig();
	$ObjectLang	= array();
	
	if (is_array($langlist)) foreach ($langlist as $key => $language):
		if (is_array($ObjectList)) foreach ($ObjectList as $items):
			$ObjectLang[$language['code']][$items['id']] = $ObjectDao->getDataLang($language['code'], $items['id']);
			if (!$ObjectLang[$language['code']][$items['id']]['name']) $ObjectLang[$language['code']][$items['id']]['name'] = $items['label'];
		endforeach;
	endforeach;

    $smarty->assign("globals",          $globals);
	$smarty->assign("ObjectList",   	$ObjectList);
	$smarty->assign("ImagesList",   	$ImagesList);
	$smarty->assign("ObjectLang",  		$ObjectLang);
	$ObjectDao->doClose();
	#==================================================================
	#==================================================================
	if (is_array($langlist)) foreach ($langlist as $key => $language):
		if (is_array($ObjectList)) foreach ($ObjectList as $items):
			if ($items['genre'] == 1):
				$oFCKContent = new FCKeditor("introtext[".$language['code']."][".$items['id']."]") ;
				$oFCKContent->ToolbarSet 		= "Default";
				$oFCKContent->BasePath 			= dirname(JURI::root()).'/library/fckeditor/';
				$oFCKContent->Height 			= "250";
				$oFCKContent->Value				= $ObjectLang[$language['code']][$items['id']]['introtext'] ? $ObjectLang[$language['code']][$items['id']]['introtext'] : null;
				$Introtext[$language['code']][$items['id']] = $oFCKContent->CreateHtml();
			endif;
		endforeach;
	endforeach;
	
	$smarty->assign("Introtext",  $Introtext);
?>