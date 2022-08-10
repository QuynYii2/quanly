<?php
/*****************************************************************
 * @project_name: localframe
 * @package: package_name
 * @file_name: contants.inc
 * @descr:
 * 
 * @version 1.0
 *****************************************************************/
if (!defined("CONTANTS_INC") ) {
	define("DATE_PHP", 				"Y-m-d");		/***********************Format date display php************************/
	Define("DATE_POPUP", 			"yyyy-mm-dd"); 	/***********************Format date display popup**********************/
	define("DATE_MYSQL", 			"%Y-%m-%d");	/***********************Format date display mysql**********************/
	define("DATE_MATCH", 			"%W %d-%m-%Y");
	define("START_DATE", 			date("Y"));
	define("END_DATE", 				date("Y")+2);
	define("IMG_FORMAT", 			"jpg,gif,png,bmp,jpeg");
	define("AUD_FORMAT", 			"mp3,wma,wav,mid");
	define("FLV_FORMAT", 			"flv,swf,mpg,wma");
	define("MAX_UPLOAD_FILE_IMG", 	10000);
	define("MAX_UPLOAD_FILE_FLV", 	100000);
	define("BR_TAG", 				"<br />");

	Define("SESSION_ALIVE_TIME", 	1200);
}
?>
