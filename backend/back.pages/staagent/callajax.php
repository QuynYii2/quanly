<?php
defined("DS") or die("Errors System");
require_once 'controler.php';
$smarty->config_load("staagent.conf");

if ($task):
    $controler = new controler();
    $controler->$task();
endif;

exit();
?>