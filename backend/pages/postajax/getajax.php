<?php
	defined("DS") or die("Errors System");
	require_once 'controler.php';
	
	if ($task):
		$controler = new controler();
		$controler->$task();
	endif;
?>