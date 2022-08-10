<?php
	$library_dir = dirname(__FILE__);
    #error_reporting(E_ALL);
    #ini_set('display_errors', 1);

	/*** include the libraries joomla class ***/
	require_once $library_dir.DS."joomla".DS."object.php";
	require_once $library_dir.DS."joomla".DS."string.php";
	require_once $library_dir.DS."joomla".DS."uri.php";
	require_once $library_dir.DS."joomla".DS."filter".DS."input.php";
	require_once $library_dir.DS."joomla".DS."filter".DS."output.php";
	/*************** Config ***************/
	require_once $library_dir . DS."config.php";
	/*************** Connect to DB ***************/
	require_once $library_dir . DS."connection.php";
	/*************** Constant ***************/
	require_once $library_dir . DS."constants.php";
	/*************** Init smarty ***************/
	require_once $library_dir . DS."Smarty".DS."Smarty.class.php";
	/*************** PEAR ***************/
	require_once $library_dir . DS."PEAR".DS."PEAR.php";
	/*************** DB of PEAR ***************/
	require_once $library_dir . DS."PEAR".DS."DB.php";
	/*************** ADODB ***************/
	require_once $library_dir . DS."PEAR".DS."adodb".DS."dbpear.php";
	/*************** PAGER ***************/
	require_once $library_dir . DS."PEAR".DS."Pager".DS."Pager.php";
	/*************** FCK Editor ***************/
	require_once $library_dir . DS."fckeditor".DS."fckeditor.php";
	/*************** Utility ***************/
	require_once $library_dir . DS."utility".DS."generals.php";
	require_once $library_dir . DS."utility".DS."router.php";
	require_once $library_dir . DS."utility".DS."images.php";
    require_once $library_dir . DS."utility".DS."ImageResize.php";
	require_once $library_dir . DS."utility".DS."pagerutils.php";
	require_once $library_dir . DS."utility".DS."logWriter.php";
	require_once $library_dir . DS."phpmailer".DS."PHPMailerAutoload.php";
    /*************** Export, Import ***************/
    require_once $library_dir . DS."phpexcel".DS."PHPExcel.php";
    require_once $library_dir . DS."mpdf".DS."mpdf.php";

	unset($library_dir);
