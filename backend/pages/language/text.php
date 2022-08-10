<?php
	defined("DS") or die("Errors System");
	require_once 'controler.php';
	#==================================================================
	#==================================================================
	$cid = Generals::getVar('cid', array());
    $genre = Generals::getVar('genre', 'front');
	$toolbar.= Generals::getToolBar('apply', 		Generals::getTitle('TOOLBAR_APPLY'), 		'apply');
	$toolbar.= Generals::getToolBar('update', 		Generals::getTitle('TOOLBAR_UPDATE'), 		'save');
	$toolbar.= Generals::getToolBar('cancel', 		Generals::getTitle('TOOLBAR_CANCEL'), 		'cancel');
	$smarty->assign("toolbar",  $toolbar);
	if ($cid[0]) $smarty->assign("TitlePage", Generals::getTitle('MENU_LANGUAGE') ." <span>[".Generals::getTitle('MENU_SUB_EDIT')."]</span>");
	else $smarty->assign("TitlePage", Generals::getTitle('MENU_LANGUAGE') ." <span>[".Generals::getTitle('MENU_SUB_ADD')."]</span>");
	$smarty->config_load("language.conf");
	#==================================================================
	#==================================================================
	if ($task):
		$controler = new controler($genre);
		$controler->$task();
	endif;
	#==================================================================
	#==================================================================
	
	$ObjectDao 	= new LanguageDao();
	$ObjectData = $ObjectDao->getData();
	
	#----------------------------------------------------------------------
	# Doc noi dung tu file
	#----------------------------------------------------------------------
    if ($genre == 'front') {
        $handle = @fopen(ROOT_PATH . DS . 'frontend' . DS . 'language' . DS . $ObjectData['code'] . DS . 'generals.conf', "r");
        if ($handle) {
            $index = 0;
            while (!feof($handle)) {
                $buffer = fgets($handle, 4096);
                $record = explode("=", $buffer, 2);
                foreach ($record as $key => $value) $record[$key] = trim($record[$key]);
                #----------------------------------------------------------------------
                # Neu khong phai la dong comment thi hien thi de nhap
                #----------------------------------------------------------------------
                if (substr($record[0], 0, 1) != "#" && strlen($record[0])) {
                    $ObjectText[$index]['title'] = $record[0];
                    $ObjectText[$index]['value'] = $record[1];
                    $index++;
                }
            }
            fclose($handle);
        }
    } else {
        $files = JFolder::files(ROOT_PATH . DS . 'backend' . DS . 'language' . DS . $ObjectData['code']);
        if (is_array($files)) foreach ($files as $file):
            $modname = basename($file, '.conf');
            $modules[$modname] = Generals::getTitle('MENU_'.strtoupper($modname));
            $handle = @fopen(ROOT_PATH . DS . 'backend' . DS . 'language' . DS . $ObjectData['code'] . DS . $file, "r");
            if ($handle) {
                $index = 0;
                while (!feof($handle)) {
                    $buffer = fgets($handle, 4096);
                    $record = explode("=", $buffer, 2);
                    foreach ($record as $key => $value) $record[$key] = trim($record[$key]);
                    #----------------------------------------------------------------------
                    # Neu khong phai la dong comment thi hien thi de nhap
                    #----------------------------------------------------------------------
                    if (substr($record[0], 0, 1) != "#" && strlen($record[0])) {
                        $ObjectText[$modname][$index]['title'] = $record[0];
                        $ObjectText[$modname][$index]['value'] = $record[1];
                        $index++;
                    }
                }
                fclose($handle);
            }
        endforeach;
    }

	$smarty->assign("ObjectData",   	$ObjectData);
	$smarty->assign("ObjectText",   	$ObjectText);
    $smarty->assign("modules",   	    $modules);
    $smarty->assign("genre",   	        $genre);
	$ObjectDao->doClose();
	#==================================================================
	#==================================================================
?>