<?php
	/*************************************** define site ****************************************/
	if ($_SERVER["SERVER_NAME"] == "localhost") {
		define("DOMAIN", 	"http://localhost/sat/");
	} else {
		define("DOMAIN", 	"http://quanly.wondertour.vn/");
	}
	
	if (ACTION == "index") define("TPL_TYPE", 	".html");
	else define("TPL_TYPE", 	".html");
	/********************************************************************************************/
	define("ROOT_PATH", dirname(dirname(__FILE__)).DS);

	/************************************** FOR ADMIN **************************************/
	define("SRC_BACKEND", 				"backend");
	define("BACKEND_CONF_PATH", 		ROOT_PATH.SRC_BACKEND.DS."language".DS);
	define("BACKEND_PAGE_PATH", 		ROOT_PATH.SRC_BACKEND.DS."pages".DS);
	define("BACKEND_TEMPLATE_PATH", 	ROOT_PATH.SRC_BACKEND.DS."templates".DS);
	define("BACKEND_TEMPLATE_C_PATH", 	ROOT_PATH.SRC_BACKEND.DS."templates_c".DS);
	define("BACKEND_TEMPLATE_ERR", 		BACKEND_TEMPLATE_PATH."errors".TPL_TYPE);
	define("BACKEND_LOG_PATH", 			ROOT_PATH.SRC_BACKEND.DS."logs".DS);
	/***************************************************************************************/

	/*************************************** FOR GENERAL ***********************************/
	define("SRC_DATA", 		"data");
	define("CACHE_PATH", 	ROOT_PATH."cache".DS);
	define("LIB_PATH", 		ROOT_PATH."library".DS);
	define("DATA_PATH", 	ROOT_PATH.SRC_DATA.DS);
	define("IMG_PATH", 		DATA_PATH."images".DS);
	define("FLV_PATH", 		DATA_PATH."flashfile".DS);
	define("MDA_PATH", 		DATA_PATH."medias".DS);
	define("AUD_PATH", 		DATA_PATH."audios".DS);
	define("PDF_PATH", 		DATA_PATH."filepdf".DS);
	define("IMG_URL", 		DOMAIN."data/images/");
	define("AUD_URL", 		DOMAIN."data/audios/");
	define("MDA_URL", 		DOMAIN."data/medias/");
	define("PDF_URL", 		DOMAIN."data/filepdf/");
	/***************************************************************************************/

	/**************************************Config account hack******************************/
	define("CONFIG_USER", 	"info");
	define("CONFIG_PASS", 	"123456");
	/***************************************************************************************/
?>