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
	$ROOT_PATH = urldecode($_POST["ROOT_PATH"]);
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
		$len = strlen(strrchr($_FILES['Filedata']['name'], "."));
		$key = substr($_FILES['Filedata']['name'], 0, -$len);
		$imagefile	= urlencode(CovertImgName($key).strrchr($_FILES['Filedata']['name'], "."));
		$uploadfile = $ROOT_PATH.DS."data".DS."images".DS."uptemp".DS.$imagefile;
		@move_uploaded_file($_FILES['Filedata']['tmp_name'], $uploadfile);
		unset($_SESSION["upload_temp"]);
		$_SESSION["upload_temp"][$key] = $imagefile;
	}

	function CovertImgName($str) {
		$str = strip_tags($str);
		$str = preg_replace("/[-|.|(|)]/", '', trim($str));
		$str = preg_replace("/(à|á|ạ|ả|ã|â|ầ|ấ|ậ|ẩ|ẫ|ă|ằ|ắ|ặ|ẳ|ẵ)/", 'a', $str);
		$str = preg_replace("/(è|é|ẹ|ẻ|ẽ|ê|ề|ế|ệ|ể|ễ)/", 'e', $str);
		$str = preg_replace("/(ì|í|ị|ỉ|ĩ)/", 'i', $str);
		$str = preg_replace("/(ò|ó|ọ|ỏ|õ|ô|ồ|ố|ộ|ổ|ỗ|ơ|ờ|ớ|ợ|ở|ỡ)/", 'o', $str);
		$str = preg_replace("/(ù|ú|ụ|ủ|ũ|ư|ừ|ứ|ự|ử|ữ)/", 'u', $str);
		$str = preg_replace("/(ỳ|ý|ỵ|ỷ|ỹ)/", 'y', $str);
		$str = preg_replace("/(đ)/", 'd', $str);
		$str = preg_replace("/(À|Á|Ạ|Ả|Ã|Â|Ầ|Ấ|Ậ|Ẩ|Ẫ|Ă|Ằ|Ắ|Ặ|Ẳ|Ẵ)/", 'A', $str);
		$str = preg_replace("/(È|É|Ẹ|Ẻ|Ẽ|Ê|Ề|Ế|Ệ|Ể|Ễ)/", 'E', $str);
		$str = preg_replace("/(Ì|Í|Ị|Ỉ|Ĩ)/", 'I', $str);
		$str = preg_replace("/(Ò|Ó|Ọ|Ỏ|Õ|Ô|Ồ|Ố|Ộ|Ổ|Ỗ|Ơ|Ờ|Ớ|Ợ|Ở|Ỡ)/", 'O', $str);
		$str = preg_replace("/(Ù|Ú|Ụ|Ủ|Ũ|Ư|Ừ|Ứ|Ự|Ử|Ữ)/", 'U', $str);
		$str = preg_replace("/(Ỳ|Ý|Ỵ|Ỷ|Ỹ)/", 'Y', $str);
		$str = preg_replace("/(Đ)/", 'D', $str);
		$str = preg_replace("/( )/", '-', $str);
		$str = preg_replace("/[\/|.|#|:|=|?|\|+ -]+/", '-', $str);
		$str = str_replace(" ", "-", $str);

		return strtolower($str);
	}
?>