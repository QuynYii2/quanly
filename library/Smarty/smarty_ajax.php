<?php
$ajax_export_list = array();

function ajax_register() {
    // Functions being called via AJAX need to be registered, to prevent harmful code from slipping through
    global $ajax_export_list;
    $n = func_num_args();
    for ($i = 0; $i < $n; $i++) {
        $ajax_export_list[] = func_get_arg($i);
    }
}

function ajax_process_call() {
    global $ajax_export_list;
    if (!isset($_REQUEST['f'])) return;
    $function = $_REQUEST['f'];
    $object = $_REQUEST['o'];

    if (!empty($object)) {
        $object = "$".$object;
        if (false !== array_search(array($object, $function), $ajax_export_list)) {
            eval ("global $object; if (is_callable(array($object, $function))) ".$object."->".$function."();");
        }
        exit();
    } else {
        if (false !== array_search($function, $ajax_export_list)) call_user_func($function);
        exit();
    }
}
?>