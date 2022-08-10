<?php
	/********************************************
	 * Created on Aug 4, 2010
	 * Package:	package_name
	 * File:	upload.php
	 * note:	
	 * @version:1.0
	 ********************************************/
	ob_start ();
	define("DS", DIRECTORY_SEPARATOR);
	set_include_path("include_path");

	if (isset($_POST["PHPSESSID"])) session_id($_POST["PHPSESSID"]);
	session_start();
	
	$len = strlen(strrchr($_POST["name"], "."));
	$key = substr($_POST["name"], 0, -$len);
	
	$result = array ("iname" => $key, "image" => $_SESSION["upload_temp"][$key]);

	echo json_encode($result);
	
	exit();
?>