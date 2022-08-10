<?php
	defined("DS") or die("Errors System");
	require_once 'controler.php';
    $smarty->config_load("invoice.conf");
	#==================================================================
	#==================================================================
	$cid = Generals::getVar('cid', array());
    $toolbar.= Generals::getToolBar('export', 		Generals::getTitle('TOOLBAR_EXPORT'), 		'export');
	$toolbar.= Generals::getToolBar('apply', 		Generals::getTitle('TOOLBAR_APPLY'), 		'apply');
	$toolbar.= Generals::getToolBar('update', 		Generals::getTitle('TOOLBAR_UPDATE'), 		'save');
	if ($cid[0]) $toolbar.= Generals::getToolBar('delete', Generals::getTitle('TOOLBAR_DELETE'),'delete');
	$toolbar.= Generals::getToolBar('cancel', 		Generals::getTitle('TOOLBAR_CANCEL'), 		'cancel');
	$smarty->assign("toolbar",  $toolbar);
	if ($cid[0]) $smarty->assign("TitlePage", Generals::getTitle('MENU_INVOICE') ." <span>[".Generals::getTitle('MENU_SUB_EDIT')."]</span>");
	else $smarty->assign("TitlePage", Generals::getTitle('MENU_INVOICE') ." <span>[".Generals::getTitle('MENU_SUB_ADD')."]</span>");
	#==================================================================
	#==================================================================
	if ($task):
		$controler = new controler();
		$controler->$task();
	endif;
	#==================================================================
	#==================================================================
    $user	    = Generals::getUserData();
	$ObjectDao 	= new InvoiceDao();
	$ObjectData = $ObjectDao->getData();
	$Ordering 	= $ObjectDao->getOrdering();
    $Agencies   = $ObjectDao->getAgencies();
    $Payments   = $ObjectDao->getPayments();

    $ObjectData['issued_by'] = $ObjectData['issued_by'] ? $ObjectData['issued_by'] : $user['name'];
    $ObjectData['issued_on'] = $ObjectData['issued_on'] ? $ObjectData['issued_on'] : date('Y-m-d');
    $ObjectData['introtext'] = is_array($ObjectData['introtext']) ? $ObjectData['introtext'] : json_decode($ObjectData['introtext'], true);

    if (is_array($ObjectData['introtext']['quote'])) foreach ($ObjectData['introtext']['quote'] as $key => $item):
        if ($item['id']):
            $query = ' SELECT a.*, b.name, (Select sum(price) From tbl_quotation_payment AS p Where p.quotation = a.id AND p.genre = 0) AS paid FROM tbl_quotation AS a ';
            $query.= ' INNER JOIN tbl_quotation_lang AS b ON a.id = b.quotation ';
            $query.= ' LEFT JOIN tbl_language AS l ON l.code = b.language WHERE a.id = ? AND b.language = ? ';

            $ObjectDao->prepare($query);
            $result = $ObjectDao->exec(array($item['id'], Generals::getSession('langcode')));
            $ObjectData['introtext']['quote'][$key]['paid'] = floatval($result[0]['paid']);
        endif;
    endforeach;

    #==================================================================
    # Keep Data If Error Handle =======================================
    #==================================================================
    $ObjectData = Generals::getState('data.form') ? Generals::getState('data.form') : $ObjectData;
    $Quotations = $ObjectData['agency'] ? $ObjectDao->getQuotations($ObjectData['agency']) : array();
    #==================================================================
    #==================================================================

	$smarty->assign("ObjectData",   	$ObjectData);
	$smarty->assign("Ordering",  		$Ordering);
    $smarty->assign("Agencies",  		$Agencies);
    $smarty->assign("Payments",  		$Payments);
    $smarty->assign("Quotations",  		$Quotations);
	$ObjectDao->doClose();
	#==================================================================
	#==================================================================
?>