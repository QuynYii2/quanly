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
	#-----------------------------------------------------------------------------------------
	# Neu upload khong thanh cong
	#-----------------------------------------------------------------------------------------
	if (!isset($_FILES["Filedata"]) || !is_uploaded_file($_FILES["Filedata"]["tmp_name"]) || $_FILES["Filedata"]["error"] != 0) {
		header("HTTP/1.1 500 File Upload Error");
		if (isset($_FILES["Filedata"])) echo $_FILES["Filedata"]["error"];
		exit(0);
	}
	#-----------------------------------------------------------------------------------------
	# Upload thanh cong
	#-----------------------------------------------------------------------------------------
	else {
		$uploadfile = base64_decode($_SESSION['UPLOAD_PATH']).DS.$_FILES['Filedata']['name'];
		@move_uploaded_file($_FILES['Filedata']['tmp_name'], $uploadfile);
	}
?>