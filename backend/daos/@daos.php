<?php
if (!defined("DAO_FILE_INC")) {
	define("DAO_FILE_INC",1);
	
	require_once LIB_PATH."joomla".DS."filesystem".DS."folder.php";
	require_once LIB_PATH."joomla".DS."filesystem".DS."file.php";
	$_files = JFolder::files(dirname(__FILE__));
	if (is_array($_files)) foreach ($_files as $_file):
		if ($_file != '@daos.php') require_once $_file;
	endforeach;
}
?>