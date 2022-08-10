<?php
$ObjectDao  = new ConfigDao();
$smarty->assign("TitlePage", $smarty->_config[0]["vars"][strtoupper("MENU_".$option)]);

if ($task == 'logout'):
	Generals::setSession('LOGIN_DATA', array());
	header("Location: index.php");
elseif ($task == 'update'):
	$primary 	= Generals::getVar('primary');
	$config 	= Generals::getVar('config');
	$label 		= Generals::getVar('label');
	$value 		= Generals::getVar('value');
	$ordering 	= Generals::getVar('ordering');
	$published 	= Generals::getVar('published');
	$user		= Generals::getUserData();
	
	foreach ($primary as $key => $val):
		$data = array();
		$data['id'] 		= $val;
		$data['config'] 	= $config[$key];
		$data['label'] 		= $label[$key];
		$data['value'] 		= $value[$key];
		$data['ordering'] 	= $ordering[$key];
		$data['published'] 	= $published[$key];
		$data['update_on'] 	= date('Y-m-d H:i:s');
		$data['update_by'] 	= $user['id'];
		
		$data = $ObjectDao->getDataMapTable($data, 'tbl_config');
		$ObjectDao->beginTrans();
		try {
			if ($data['id']):
				$ObjectDao->update_db('tbl_config', $data, ' id = ? ', array($data['id']));
			else:
				$ObjectDao->insert_db('tbl_config', $data);
			endif;
	        
			$ObjectDao->commitTrans();
			Generals::setError(Generals::getTitle('UPDATE_SUCCESS'));
		} catch (Exception $ex) {
			$ObjectDao->rollbackTrans();
			Generals::setError(Generals::getTitle('UPDATE_EROR'));
			Generals::redirect('index.php');
		}
	endforeach;
	
	Generals::redirect('index.php');
endif;

#$toolbar.= Generals::getToolBar('update', Generals::getTitle('TOOLBAR_UPDATE'), 'save');
#$smarty->assign("toolbar",  $toolbar);

$globals	= $ObjectDao->getGlobals();
$smarty->assign("globals",  $globals);
?>